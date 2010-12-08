<?php
class org_midgardproject_news_controllers_item extends midgardmvc_core_controllers_baseclasses_crud
{
    public function load_object(array $args)
    {
        $this->object = new org_midgardproject_news_article($args['item']);
    }
    
    public function prepare_new_object(array $args)
    {
        $this->object = new org_midgardproject_news_article();
    }
    
    public function get_url_read()
    {
        return midgardmvc_core::get_instance()->dispatcher->generate_url
        (
            'item_read', array
            (
                'item' => $this->object->guid
            ),
            $this->request
        );
    }
    
    public function get_url_update()
    {
        return midgardmvc_core::get_instance()->dispatcher->generate_url
        (
            'item_update', array
            (
                'item' => $this->object->guid
            ),
            $this->request
        );
    }

    public function load_form()
    {
        $this->form = midgardmvc_helper_forms_mgdschema::create($this->object);

        // Make Category and Type proper selects instead
        unset($this->form->category);
        unset($this->form->type);

        $field = $this->form->add_field('category', 'text');
        $field->set_value($this->object->category);
        $widget = $field->set_widget('selectoption');
        $widget->set_label('category');
        $category_options = array();
        $categories = midgardmvc_core::get_instance()->configuration->categories;
        foreach ($categories as $category)
        {
            $category_options[] = array
            (
                'description' => ucfirst($category),
                'value' => $category,
            );
        }

        $widget->set_options($category_options);
    }
}
?>
