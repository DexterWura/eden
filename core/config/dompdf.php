<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Set some default values. It is possible to add all definied settings by
    | documenting them in this file.
    |
    */

    "mode"                       => "",
    "format"                     => "A4",
    "default_font_size"          => "12",
    "default_font"               => "serif",
    "margin_left"                => 10,
    "margin_right"               => 10,
    "margin_top"                 => 14,
    "margin_bottom"              => 14,
    "margin_header"              => 5,
    "margin_footer"              => 5,
    "orientation"                => "portrait",
    "title"                      => "Laravel",
    "author"                     => "",
    "watermark"                  => "",
    "watermark_font"             => "",
    "display_progress"           => true,
    "default_paper_size"         => "a4",

    /*
    |--------------------------------------------------------------------------
    | Chrome
    |--------------------------------------------------------------------------
    |
    | Chrome is used to render HTML to PDF. This option allows you to set
    | the path to your chrome binary. By default, it will use the system
    | path.
    |
    */

    'chrome' => [

        'binary' => '',

        'args' => [],

    ],

];
