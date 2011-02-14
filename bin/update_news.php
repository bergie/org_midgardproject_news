<?php
$filepath = ini_get ("midgard.configuration_file");
$config = new midgard_config ();
$config->read_file_at_path($filepath);
$mgd = midgard_connection::get_instance();
$mgd->open_config($config); 

$basedir = dirname(__FILE__) . '/../..';
require("{$basedir}/midgardmvc_core/framework.php");
$mvc = midgardmvc_core::get_instance("{$basedir}/application.yml");
midgardmvc_core::get_instance()->component->load_library('Feed');

function import_item($item)
{
    $link = $item->link[0]->href;
    $update = false;
    $qb = new midgard_query_builder('org_midgardproject_news_article');
    $qb->add_constraint('url', '=', $link);
    $articles = $qb->execute();
    if (empty($articles))
    {
        // New news item
        $article = new org_midgardproject_news_article();
        $article->url = $link;
        $update = true;
    }
    else
    {
        $article = $articles[0];
    }

    if ($article->title != $item->title->text)
    {
        $article->title = $item->title->text;
        $update = true;
    }

    if ($article->content != $item->description->text)
    {
        $article->content = $item->description->text;
        $update = true;
    }

    if ($article->metadata->published->getTimestamp() != $item->published->date->getTimestamp())
    {
        $article->metadata->published->setTimestamp($item->published->date->getTimestamp());
        $update = true;
    }

    if ($update)
    {
        if (!$article->guid)
        {
            $article->create();
            return;
        }
        $article->update();
    }
}

function fetch_node_feeds(midgardmvc_core_providers_hierarchy_node $node)
{
    // Initialize context
    $request = midgardmvc_core_request::get_for_intent($node->get_path());
    $request->add_component_to_chain(midgardmvc_core::get_instance()->component->get('midgardmvc_core'));
    midgardmvc_core::get_instance()->context->create($request);
    midgardmvc_core::get_instance()->component->inject($request, 'process');

    $feeds = midgardmvc_core::get_instance()->configuration->fetch_feeds;
    if (   !$feeds
        || !is_array($feeds))
    {
        return;
    }

    foreach ($feeds as $url)
    {
        if (empty($url))
        {
            continue;
        }

        try {
            $feed = ezcFeed::parse($url);
        }
        catch (Exception $e)
        {
            continue;
        }

        $transaction = new midgard_transaction();
        $transaction->begin();
        foreach ($feed->item as $item)
        {
            import_item($item);
        }
        $transaction->commit();
    }

    midgardmvc_core::get_instance()->context->delete();
}

function check_node(midgardmvc_core_providers_hierarchy_node $node)
{
    if ($node->get_component() == 'org_midgardproject_news')
    {
        fetch_node_feeds($node);
    }

    $children = $node->get_child_nodes();
    foreach ($children as $child)
    {
        check_node($child);
    }
}

check_node(midgardmvc_core::get_instance()->hierarchy->get_root_node());
