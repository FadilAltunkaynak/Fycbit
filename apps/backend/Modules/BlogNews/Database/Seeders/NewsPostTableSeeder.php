<?php

namespace Modules\BlogNews\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\BlogNews\Entities\NewsPost;

class NewsPostTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        NewsPost::firstOrCreate(
            [
                'category' => 1
            ],
            [
                //'thumbnail' => '//https://www.shutterstock.com/image-photo/surreal-image-african-elephant-wearing-260nw-1365289022.jpg',
                'title' => 'Understanding market trends and analyzing',
                'slug' => make_unique_slug('Understanding market trends and analyzing'),
                'body'  => 'The market is a constantly changing entity, and keeping up with the latest trends can be a daunting task for any business owner or investor. However, understanding market trends and analyzing them correctly can provide valuable insights that can help you make informed decisions. In this blog post, we\'ll take a closer look at the different types of market trends, the tools and methods used for analyzing them, and how you can use this information to your advantage.

                First, it\'s important to understand the different types of market trends. There are three main types: short-term, intermediate-term, and long-term trends. Short-term trends are those that last for a few days or weeks, intermediate-term trends are those that last for a few months, and long-term trends are those that last for a year or more. By identifying these trends, you can determine which stage of the market cycle you\'re currently in, and make predictions about future market movements.
                
                One of the most popular tools for analyzing market trends is technical analysis. This method uses charts, patterns, and indicators to identify trends and make predictions about future price movements. Technical analysts believe that the market reflects all the information that is available at any given time, so by looking at historical data, they can identify patterns and make predictions about future market movements.

                Another tool for analyzing market trends is fundamental analysis. This method looks at the underlying economic and financial factors that drive the market. Analysts who use this method focus on things like company financials, economic indicators, and other data to identify trends and make predictions about future market movements.',
                'status' => STATUS_ACTIVE,
                'publish' => STATUS_ACTIVE,
                'is_fetured' => STATUS_ACTIVE,
                'comment_allow' => STATUS_ACTIVE,
                'publish_at' => date('Y-m-d H:i:s')
            ]
        );
        NewsPost::firstOrCreate(
            [
                'category' => 2
            ],
            [
                //'thumbnail' => '//https://www.shutterstock.com/image-photo/surreal-image-african-elephant-wearing-260nw-1365289022.jpg',
                'title' => 'It would also cover news and updates on regulatory',
                'slug' => make_unique_slug('It would also cover news and updates on regulatory'),
                'body'  => 'The market is a constantly changing entity, and keeping up with the latest trends can be a daunting task for any business owner or investor. However, understanding market trends and analyzing them correctly can provide valuable insights that can help you make informed decisions. In this blog post, we\'ll take a closer look at the different types of market trends, the tools and methods used for analyzing them, and how you can use this information to your advantage.

                First, it\'s important to understand the different types of market trends. There are three main types: short-term, intermediate-term, and long-term trends. Short-term trends are those that last for a few days or weeks, intermediate-term trends are those that last for a few months, and long-term trends are those that last for a year or more. By identifying these trends, you can determine which stage of the market cycle you\'re currently in, and make predictions about future market movements.
                
                One of the most popular tools for analyzing market trends is technical analysis. This method uses charts, patterns, and indicators to identify trends and make predictions about future price movements. Technical analysts believe that the market reflects all the information that is available at any given time, so by looking at historical data, they can identify patterns and make predictions about future market movements.

                Another tool for analyzing market trends is fundamental analysis. This method looks at the underlying economic and financial factors that drive the market. Analysts who use this method focus on things like company financials, economic indicators, and other data to identify trends and make predictions about future market movements.',
                'status' => STATUS_ACTIVE,
                'publish' => STATUS_ACTIVE,
                'is_fetured' => STATUS_ACTIVE,
                'comment_allow' => STATUS_ACTIVE,
                'publish_at' => date('Y-m-d H:i:s')
            ]
        );
    }
}
