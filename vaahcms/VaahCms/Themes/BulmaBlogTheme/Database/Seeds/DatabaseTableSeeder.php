<?php
namespace VaahCms\Themes\BulmaBlogTheme\Database\Seeds;


use Illuminate\Database\Seeder;
use VaahCms\Modules\Cms\Libraries\CmsSeeder;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class DatabaseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $theme_slug = 'bulmablogtheme';

        $file = __DIR__.'/json/theme_locations.json';
        CmsSeeder::themeLocations($theme_slug, $file);

        $file = __DIR__.'/json/templates.json';
        CmsSeeder::templates($theme_slug, $file);

        $file = __DIR__.'/json/content_types.json';
        CmsSeeder::contentTypes($file);

        $file = __DIR__.'/json/blogs.json';
        CmsSeeder::createSampleField($theme_slug, $file,'blog');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */




}
