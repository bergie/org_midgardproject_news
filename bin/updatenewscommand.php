<?php
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class org_midgardproject_news_bin_updatenewscommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('omn:import')
            ->setDescription('Import remote news feeds')
            ->addArgument('url', InputArgument::REQUIRED, 'URL of the feed to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        require('ezc/Base/base.php');
        spl_autoload_register(array(
            'ezcBase',
            'autoload'
        ));

        $url = $input->getArgument('url');
        $output->writeln(sprintf('Importing feed from "%s"', $url));

        $feed = ezcFeed::parse($url);
        foreach ($feed->item as $item) {
            $output->writeln(sprintf('Importing item "%s"', $item->title->text));
            $this->importItem($item);
        }

        $output->writeln('Done.');
    }

    protected function importItem($item)
    {
        $link = $item->link[0]->href;
        $update = false;
        $qb = new midgard_query_builder('org_midgardproject_news_article');
        $qb->add_constraint('url', '=', $link);
        $articles = $qb->execute();
        if (empty($articles)) {
            // New news item
            $article = new org_midgardproject_news_article();
            $article->url = $link;
            $update = true;
        } else {
            $article = $articles[0];
        }

        if ($article->title != $item->title->text) {
            $article->title = $item->title->text;
            $update = true;
        }

        if ($article->content != $item->description->text) {
            $article->content = $item->description->text;
            $update = true;
        }

        if ($article->metadata->published->getTimestamp() != $item->published->date->getTimestamp()) {
            $article->metadata->published->setTimestamp($item->published->date->getTimestamp());
            $update = true;
        }

        if ($update) {
            if (!$article->guid) {
                $article->create();
                return;
            }
            $article->update();
        }
    }
}
