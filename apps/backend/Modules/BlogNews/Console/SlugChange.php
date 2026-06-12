<?php

namespace Modules\BlogNews\Console;

use Illuminate\Console\Command;
use Modules\BlogNews\Entities\BlogPost;
use Modules\BlogNews\Entities\NewsPost;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SlugChange extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'change-slug-blog-news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        

        try {
            $blog = BlogPost::get();
            $news = NewsPost::get();
            if(!empty($blog))
            {
                $blog->map(function ($item) {
                    $item->update(['slug' => filterBlogNewsSlug($item->title ?? 'unique slug')]);
                });
            }
            if(!empty($news))
            {
                $news->map(function ($item) {
                    $item->update(['slug' => filterBlogNewsSlug($item->title ?? 'unique slug')]);
                });
                
            }
        } catch (\Exception $e) {
            storeException('changeSlugBlogNews',$e->getMessage());
        }
        

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            // ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
