<?php
class org_midgardproject_news_controllers_latest
{
    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    private function check_categories(midgard_query_builder $qb, array $args)
    {
        $categories = midgardmvc_core::get_instance()->configuration->categories;
        if (!isset($args['category']))
        {
            // Limit to the categories configured here. This allows running multiple news services from same news listing
            $qb->add_constraint('category', 'IN', $categories);
            return;
        }

        // Check that the user-provided category is valid
        if (!in_array($args['category'], $categories))
        {
            throw new midgardmvc_exception_notfound("Category {$args['category']} not found");
        }
        $qb->add_constraint('category', '=', $args['category']);
    }

    private function check_types(midgard_query_builder $qb, array $args)
    {
        $types = midgardmvc_core::get_instance()->configuration->types;

        if (isset($args['type']))
        {
            // Check that the user-provided type is valid
            if (!in_array($args['typey'], $types))
            {
                throw new midgardmvc_exception_notfound("Type {$args['type']} not found");
            }
            $qb->add_constraint('type', '=', $args['type']);
        }
    }

    public function get_items(array $args)
    {
        $qb = new midgard_query_builder('org_midgardproject_news_article');
        $this->check_categories($qb, $args);

        if (isset($args['type']))
        {
            // TODO: Check for valid types
            $qb->add_constraint('type', '=', $args['type']);
        }

        $qb->add_order('metadata.published', 'DESC');
        $qb->set_limit(midgardmvc_core::get_instance()->configuration->index_items);
        $this->data['items'] = $qb->execute();
        $this->data['title'] = 'Latest news';
    }
}
