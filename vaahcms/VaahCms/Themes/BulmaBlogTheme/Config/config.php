<?php

return [
    "name"=> "BulmaBlogTheme",
    "title"=> "Bulma 0.9.2 Blog",
    "slug"=> "bulmablogtheme",
    "thumbnail"=> "https://placehold.jp/300x160.png",
    "excerpt"=> "Bulma 0.9.2 Blog",
    "description"=> "Bulma 0.9.2 Blog",
    "download_link"=> "",
    "author_name"=> "bulmablogtheme",
    "author_website"=> "https://vaah.dev",
    "version"=> "0.0.6",
    "is_migratable"=> true,
    "is_sample_data_available"=> true,
    "db_table_prefix"=> "vh_bulmablogtheme_",
    "providers"=> [
        "\\VaahCms\\Themes\\BulmaBlogTheme\\Providers\\BulmaBlogThemeServiceProvider"
    ],
    "aside-menu-order"=> null
];
