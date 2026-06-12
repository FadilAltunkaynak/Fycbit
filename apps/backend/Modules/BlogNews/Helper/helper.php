<?php

use Modules\BlogNews\Entities\BlogCategory;
use Modules\BlogNews\Entities\NewsCategory;

const BLOG_THUMBNAIL_PATH = 'storage/blog_news/blog/thumbnail/';
const NEWS_THUMBNAIL_PATH = 'storage/blog_news/news/thumbnail/';

const PUBLIC_LINK = 'storage/blog_news/assets';
const PUBLIC_BLOG_FILEMANEGER = PUBLIC_LINK . '/blog_filemanager';

const TYPE_MAIN_CATEGORY = 1;
const TYPE_SUB_CATEGORY = 2;

const TYPE_BLOG_RECENT = 1;
const TYPE_BLOG_POPULER = 2;
const TYPE_BLOG_FEATURED = 3;

const TYPE_NEWS_RECENT = 1;
const TYPE_NEWS_POPULER = 2;

const CUSTOM_PAGE_BLOG = 1;
const CUSTOM_PAGE_NEWS = 0;

function mainCategoryTitle($id)
{
    try {
        $data = BlogCategory::find($id);
        if ($data) {
            return $data->title ?? '';
        } else {
            return __("not found");
        }
    } catch (\Exception $e) {
        storeException('mainCategoryTitle', $e->getMessage());
        return __("not found");
    }
}
function mainNewsCategoryTitle($id)
{
    try {
        $data = NewsCategory::find($id);
        if ($data) {
            return $data->title ?? '';
        } else {
            return __("not found");
        }
    } catch (\Exception $e) {
        storeException('mainCategoryTitle', $e->getMessage());
        return __("not found");
    }
}

function ActionButtonForList($id, $eidt_url, $delete_url)
{
    try {
        $html = '<li class="deleteuser"><a title="' . __('Edit') . '" href="' . route($eidt_url, ['id' => $id]) . '" data-toggle="modal"><span class=""><i class="fa fa-pencil" aria-hidden="true"></i>
        </span></a> </li>';
        $html .= '<li class="deleteuser"><a title="' . __('Delete') . '" href="#delete_' . ($id) . '" data-toggle="modal"><span class=""><i class="fa fa-trash" aria-hidden="true"></i>
        </span></a> </li>';
        $html .= '<div id="delete_' . ($id) . '" class="modal fade delete" role="dialog">';
        $html .= '<div class="modal-dialog modal-sm">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
        $html .= '<form action="' . route($delete_url, ['id' => $id]) . '"method="post">';
        $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
        $html .= '<input type="hidden" name="id" value="' . $id . '" />';
        $html .= '<div class="modal-body">';
        $html .= '<p>' . __('Do you want to Delete?') . '</p>';
        $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
        $html .= '<button class="btn btn-danger" type="submit">' . __('Delete') . '</button>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    } catch (\Exception $e) {
        storeException('ActionButtonForList', $e->getMessage());
        return 'N/A';
    }
}

function ActionButtonForBlogList($id)
{
    try {

        //$html  = '<a title="'.__("Preview").'" class="btn btn-sm btn-info" href="'.route('createBlogPage',['id' => encrypt($id)]).'"><i class="fa fa-eye fa-lg"></i></a>  ';
        $html = '<a title="' . __("Comments") . '" class="btn btn-sm btn-primary" href="' . route('commentList', ['id' => $id]) . '">Comments</a>  ';
        $html .= '<a title="' . __("Edit") . '" class="btn btn-sm btn-primary" href="' . route('createBlogPage', ['id' => $id]) . '"><i class="fa fa-pencil fa-lg"></i></a>  ';
        $html .= '<a title="' . __("Delete") . '" class="btn btn-sm btn-danger" href="#delete_' . $id . '" data-toggle="modal"><i class="fa fa-trash fa-lg"></i></a>';
        $html .= '<div id="delete_' . ($id) . '" class="modal fade delete" role="dialog">';
        $html .= '<div class="modal-dialog modal-sm">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
        $html .= '<form action="' . route('deleteBlogProcess', ['id' => $id]) . '" method="post">';
        $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
        // $html .= '<input type="hidden" name="id" value="'.($id) .'" />';
        $html .= '<div class="modal-body">';
        $html .= '<p>' . __('Do you want to Delete?') . '</p>';
        $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
        $html .= '<button class="btn btn-danger" type="submit">' . __('Delete') . '</button>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    } catch (\Exception $e) {
        storeException('ActionButtonForList', $e->getMessage());
        return 'N/A';
    }
}

function ActionButtonForCommentList($comment, $editRoute = 'BlogCommentEdit', $acceptRoute = 'BlogCommentAccept', $deleteRoute = 'BlogCommentDelete', $pending_list = 1)
{
    try {
        $id = $comment->id;
        $html = '';
        if ($pending_list)
            $html .= '<a title="' . __("Accept") . '" class="btn btn-sm btn-primary" href="' . route($acceptRoute, ['id' => encrypt($id)]) . '"><i class="fa fa-check fa-lg"></i></a>  ';
        $html .= '<a title="' . __("Edit") . '" class="btn btn-sm btn-primary" href="#edit_' . $id . '" data-toggle="modal"><i class="fa fa-pencil fa-lg"></i></a>  ';
        $html .= '<a title="' . __("Delete") . '" class="btn btn-sm btn-danger" href="#delete_' . $id . '" data-toggle="modal"><i class="fa fa-trash fa-lg"></i></a>';
        $html .= '<div id="edit_' . $id . '" class="modal fade delete" role="dialog">';
        $html .= '<div class="modal-dialog modal-sm">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Edit') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
        $html .= '<form action="' . route($editRoute) . '" method="post">';
        $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
        $html .= '<input type="hidden" name="id" value="' . encrypt($id) . '" />';
        $html .= '<div class="modal-body">';
        $html .= '<input class="form-control" type="text" placeholder="' . __('Name') . '" name="name" value="' . $comment->name . '" />';
        $html .= '<input class="form-control" type="text" placeholder="' . __('Email') . '" name="email" value="' . $comment->email . '" />';
        $html .= '<input class="form-control" type="text" placeholder="' . __('Website') . '" name="website" value="' . $comment->website . '" />';
        $html .= '<input class="form-control" type="text" placeholder="' . __('Message') . '" name="message" value="' . $comment->message . '" /></div>';
        $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
        $html .= '<button class="btn btn-success" type="submit">' . __('Update') . '</button>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div id="delete_' . $id . '" class="modal fade delete" role="dialog">';
        $html .= '<div class="modal-dialog modal-sm">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
        $html .= '<form action="' . route($deleteRoute, ['id' => encrypt($id)]) . '" method="get">';
        $html .= '<div class="modal-body">';
        $html .= '<p>' . __("Are you sure want to delete this comment ?") . '</p></div>';
        $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
        $html .= '<button class="btn btn-danger" type="submit">' . __('Delete') . '</button>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    } catch (\Exception $e) {
        storeException('ActionButtonForList', $e->getMessage());
        return 'N/A';
    }
}
function ActionButtonForNewsList($id)
{
    try {
        // $html  = '<a title="'.__("Preview").'" class="btn btn-sm btn-info" href="'.route('createBlogPage',['id' => encrypt($id)]).'"><i class="fa fa-eye fa-lg"></i></a>  ';
        $html = '<a title="' . __("Comments") . '" class="btn btn-sm btn-primary" href="' . route('newsCommentList', ['id' => $id]) . '">Comments</a>  ';
        $html .= '<a title="' . __("Edit") . '" class="btn btn-sm btn-primary" href="' . route('createNewsPage', ['id' => $id]) . '"><i class="fa fa-pencil fa-lg"></i></a>  ';
        $html .= '<a title="' . __("Delete") . '" class="btn btn-sm btn-danger" href="#delete_' . $id . '" data-toggle="modal"><i class="fa fa-trash fa-lg"></i></a>';
        $html .= '<div id="delete_' . ($id) . '" class="modal fade delete" role="dialog">';
        $html .= '<div class="modal-dialog modal-sm">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
        $html .= '<form action="' . route('deleteNewsProcess', ['id' => $id]) . '"method="post">';
        $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
        //$html .= '<input type="hidden" name="id" value="'.decrypt($id) .'" />';
        $html .= '<div class="modal-body">';
        $html .= '<p>' . __('Do you want to Delete?') . '</p>';
        $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
        $html .= '<button class="btn btn-danger" type="submit">' . __('Delete') . '</button>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    } catch (\Exception $e) {
        storeException('ActionButtonForList', $e->getMessage());
        return 'N/A';
    }
}
function ActionButtonForPageList($type, $id)
{
    try {
        // $html  = '<a title="'.__("Preview").'" class="btn btn-sm btn-info" href="'.route('createBlogPage',['id' => encrypt($id)]).'"><i class="fa fa-eye fa-lg"></i></a>  ';
        $html = '<a title="' . __("Edit") . '" class="btn btn-sm btn-primary" href="' . route('createCustomPage', ['type' => $type, 'id' => encrypt($id)]) . '"><i class="fa fa-pencil fa-lg"></i></a>  ';
        $html .= '<a title="' . __("Delete") . '" class="btn btn-sm btn-danger" href="#delete_' . $id . '" data-toggle="modal"><i class="fa fa-trash fa-lg"></i></a>';
        $html .= '<div id="delete_' . ($id) . '" class="modal fade delete" role="dialog">';
        $html .= '<div class="modal-dialog modal-sm">';
        $html .= '<div class="modal-content">';
        $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
        $html .= '<form action="' . route('customPagesDelete', ['type' => $type, 'id' => $id]) . '"method="post">';
        $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
        $html .= '<input type="hidden" name="id" value="' . $id . '" />';
        $html .= '<div class="modal-body">';
        $html .= '<p>' . __('Do you want to Delete?') . '</p>';
        $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
        $html .= '<button class="btn btn-danger" type="submit">' . __('Delete') . '</button>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    } catch (\Exception $e) {
        storeException('ActionButtonForList', $e->getMessage());
        return 'N/A';
    }
}

function filterBlogNewsSlug($slug)
{
    return str_replace(" ", "-", strtolower(preg_replace("%(['\(\)\[\]\";:'])%im", "", $slug))) . "-" . uniqid();
}

function translationActionButtonBlogNews($route_name, $id = null)
{
    try {

        $html = '<a title="' . __("Update Languages") . '" class="btn btn-sm btn-primary" href="' . route($route_name, ['id' => encrypt($id)]) . '">' . __("Update Languages") . '</a>  ';
        return $html;
    } catch (\Exception $e) {
        storeException('translationActionButtonBlogNews', $e->getMessage());
        return 'N/A';
    }
}
