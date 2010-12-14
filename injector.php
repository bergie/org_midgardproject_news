<?php
/**
 * @package org_midgardproject_news
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * News category handler
 *
 * @package org_midgardproject_news
 */
class org_midgardproject_news_injector
{
    public function inject_process(midgardmvc_core_request $request)
    {
        // Subscribe to content changed signals from Midgard
        midgard_object_class::connect_default('org_midgardproject_news_article', 'action-create', array('org_midgardproject_news_injector', 'check'), array($request));
    }

    public static function check(org_midgardproject_news_article $article, $params)
    {
        if (!$article->category)
        {
            $categories = midgardmvc_core::get_instance()->configuration->categories;
            $article->category = $categories[0];
        }
    }
}
?>
