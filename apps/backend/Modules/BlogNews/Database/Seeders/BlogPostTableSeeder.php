<?php

namespace Modules\BlogNews\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\BlogNews\Entities\BlogPost;

class BlogPostTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        Model::unguard();
        BlogPost::firstOrCreate(
            [
                'slug' => make_unique_slug('Unlocking the Secrets of Market Trends: A Comprehensive Analysis'),
            ],
            [
                //'thumbnail' => '//https://www.shutterstock.com/image-photo/surreal-image-african-elephant-wearing-260nw-1365289022.jpg',
                'title' => 'Unlocking the Secrets of Market Trends: A Comprehensive Analysis',
                'slug' => make_unique_slug('Unlocking the Secrets of Market Trends: A Comprehensive Analysis'),
                'body'  => 'The market is a constantly changing entity, and keeping up with the latest trends can be a daunting task for any business owner or investor. However, understanding market trends and analyzing them correctly can provide valuable insights that can help you make informed decisions. In this blog post, we\'ll take a closer look at the different types of market trends, the tools and methods used for analyzing them, and how you can use this information to your advantage.

                First, it\'s important to understand the different types of market trends. There are three main types: short-term, intermediate-term, and long-term trends. Short-term trends are those that last for a few days or weeks, intermediate-term trends are those that last for a few months, and long-term trends are those that last for a year or more. By identifying these trends, you can determine which stage of the market cycle you\'re currently in, and make predictions about future market movements.
                
                One of the most popular tools for analyzing market trends is technical analysis. This method uses charts, patterns, and indicators to identify trends and make predictions about future price movements. Technical analysts believe that the market reflects all the information that is available at any given time, so by looking at historical data, they can identify patterns and make predictions about future market movements.

                Another tool for analyzing market trends is fundamental analysis. This method looks at the underlying economic and financial factors that drive the market. Analysts who use this method focus on things like company financials, economic indicators, and other data to identify trends and make predictions about future market movements.',
                'status' => STATUS_ACTIVE,
                'publish' => STATUS_ACTIVE,
                'is_fetured' => STATUS_ACTIVE,
                'comment_allow' => STATUS_ACTIVE,
                'publish_at' => date('Y-m-d H:i:s'),
                'category' => 1,
                'sub_category' => 4,
            ]
        );
        BlogPost::firstOrCreate(
            [
                'slug' => make_unique_slug('Navigating the Complexities of Market Analysis: A Beginner\'s Guide'),
            ],
            [
                //'thumbnail' => '//https://www.shutterstock.com/image-photo/surreal-image-african-elephant-wearing-260nw-1365289022.jpg',
                'title' => 'Navigating the Complexities of Market Analysis: A Beginner\'s Guide',
                'slug' => make_unique_slug('Navigating the Complexities of Market Analysis: A Beginner\'s Guide'),
                'body'  => 'The market is a constantly changing entity, and keeping up with the latest trends can be a daunting task for any business owner or investor. However, understanding market trends and analyzing them correctly can provide valuable insights that can help you make informed decisions. In this blog post, we\'ll take a closer look at the different types of market trends, the tools and methods used for analyzing them, and how you can use this information to your advantage.

                First, it\'s important to understand the different types of market trends. There are three main types: short-term, intermediate-term, and long-term trends. Short-term trends are those that last for a few days or weeks, intermediate-term trends are those that last for a few months, and long-term trends are those that last for a year or more. By identifying these trends, you can determine which stage of the market cycle you\'re currently in, and make predictions about future market movements.
                
                One of the most popular tools for analyzing market trends is technical analysis. This method uses charts, patterns, and indicators to identify trends and make predictions about future price movements. Technical analysts believe that the market reflects all the information that is available at any given time, so by looking at historical data, they can identify patterns and make predictions about future market movements.

                Another tool for analyzing market trends is fundamental analysis. This method looks at the underlying economic and financial factors that drive the market. Analysts who use this method focus on things like company financials, economic indicators, and other data to identify trends and make predictions about future market movements.',
                'status' => STATUS_ACTIVE,
                'publish' => STATUS_ACTIVE,
                'is_fetured' => STATUS_ACTIVE,
                'comment_allow' => STATUS_ACTIVE,
                'publish_at' => date('Y-m-d H:i:s'),
                'category' => 1,
                'sub_category' => 5,
            ]
        );
        BlogPost::firstOrCreate(
            [
                'slug' => make_unique_slug('Understanding market trends and analyzing'),

            ],
            [
                //'thumbnail' => '//https://www.shutterstock.com/image-photo/surreal-image-african-elephant-wearing-260nw-1365289022.jpg',
                'title' => 'Title: "Understanding market trends and analyzing',
                'slug' => make_unique_slug('Understanding market trends and analyzing'),
                'body'  => 'The market is a constantly changing entity, and keeping up with the latest trends can be a daunting task for any business owner or investor. However, understanding market trends and analyzing them correctly can provide valuable insights that can help you make informed decisions. In this blog post, we\'ll take a closer look at the different types of market trends, the tools and methods used for analyzing them, and how you can use this information to your advantage.

                First, it\'s important to understand the different types of market trends. There are three main types: short-term, intermediate-term, and long-term trends. Short-term trends are those that last for a few days or weeks, intermediate-term trends are those that last for a few months, and long-term trends are those that last for a year or more. By identifying these trends, you can determine which stage of the market cycle you\'re currently in, and make predictions about future market movements.
                
                One of the most popular tools for analyzing market trends is technical analysis. This method uses charts, patterns, and indicators to identify trends and make predictions about future price movements. Technical analysts believe that the market reflects all the information that is available at any given time, so by looking at historical data, they can identify patterns and make predictions about future market movements.

                Another tool for analyzing market trends is fundamental analysis. This method looks at the underlying economic and financial factors that drive the market. Analysts who use this method focus on things like company financials, economic indicators, and other data to identify trends and make predictions about future market movements.',
                'status' => STATUS_ACTIVE,
                'publish' => STATUS_ACTIVE,
                'is_fetured' => STATUS_ACTIVE,
                'comment_allow' => STATUS_ACTIVE,
                'publish_at' => date('Y-m-d H:i:s'),
                'category' => 2,
                'sub_category' => 6,
            ]
        );
        BlogPost::firstOrCreate(
            [
                'slug' => make_unique_slug('Understanding market trends and analyzingg'),
            ],
            [
                //'thumbnail' => '//https://www.shutterstock.com/image-photo/surreal-image-african-elephant-wearing-260nw-1365289022.jpg',
                'title' => 'Title: "Understanding market trends and analyzing',
                'slug' => make_unique_slug('Understanding market trends and analyzingg'),
                'body'  => 'The market is a constantly changing entity, and keeping up with the latest trends can be a daunting task for any business owner or investor. However, understanding market trends and analyzing them correctly can provide valuable insights that can help you make informed decisions. In this blog post, we\'ll take a closer look at the different types of market trends, the tools and methods used for analyzing them, and how you can use this information to your advantage.

                First, it\'s important to understand the different types of market trends. There are three main types: short-term, intermediate-term, and long-term trends. Short-term trends are those that last for a few days or weeks, intermediate-term trends are those that last for a few months, and long-term trends are those that last for a year or more. By identifying these trends, you can determine which stage of the market cycle you\'re currently in, and make predictions about future market movements.
                
                One of the most popular tools for analyzing market trends is technical analysis. This method uses charts, patterns, and indicators to identify trends and make predictions about future price movements. Technical analysts believe that the market reflects all the information that is available at any given time, so by looking at historical data, they can identify patterns and make predictions about future market movements.

                Another tool for analyzing market trends is fundamental analysis. This method looks at the underlying economic and financial factors that drive the market. Analysts who use this method focus on things like company financials, economic indicators, and other data to identify trends and make predictions about future market movements.',
                'status' => STATUS_ACTIVE,
                'publish' => STATUS_ACTIVE,
                'is_fetured' => STATUS_ACTIVE,
                'comment_allow' => STATUS_ACTIVE,
                'publish_at' => date('Y-m-d H:i:s'),
                'category' => 2,
                'sub_category' => 7,
            ]
        );
    }


}
