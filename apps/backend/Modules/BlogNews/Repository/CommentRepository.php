<?php
namespace Modules\BlogNews\Repository;

use Modules\BlogNews\Entities\BlogComment;
use Modules\BlogNews\Entities\NewsComment;
use Modules\BlogNews\Entities\BlogNewsSetting;

class CommentRepository
{
    public function storeComment($data, $id, $model)
    {
        try {
            $setting = BlogNewsSetting::get()->toSlugValue();
            $checkSetting = (strpos($model, "BlogComment") !== false) ?
                'blog_auto_comment_approval' : 'news_auto_comment_approval';

            if ($setting->$checkSetting ?? true)
                $responseSuc = responseData(true, __('Comment created Successfully'));
            else
                $responseSuc = responseData(true, __('Comment pending for admin approval'));

            $responseErr = responseData(false, __('Comment created failed'));
            if ($id) {
                $responseErr = responseData(false, __('Comment update failed'));
                $responseSuc = responseData(true, __('Comment update Successfully'));
            }
            $posts = $model::updateOrCreate(['id' => $id], $data);
            if ($posts) {
                $responseSuc['data'] = [];

                $commentData = $this->getComment($model, [
                    'post_id' => (isset($data['post_id'])) ? $data['post_id'] : $id,
                ]);
                if (!$commentData['success'])
                    $responseSuc['data'] = ['message' => __('Error occurred while creating comment')];
                else
                    $responseSuc['data'] = $commentData['data'];
                return $responseSuc;
            }
            return $responseErr;

        } catch (\Exception $e) {
            storeException('StoerComment repo', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function getComment($model, $extra)
    {
        try {
            $comment = null;
            if (isset($extra['post_id']) && $extra['post_id'])
                $comment = $model::where(['post_id' => $extra['post_id'], 'status' => STATUS_ACTIVE]);
            else
                $comment = $model::where('status', STATUS_ACTIVE);

            $comment = $comment->orderBy('created_at', 'DESC')->get();
            $comment->map(function ($row) {
                $row->to = encrypt($row->id);
            });

            $mainComment = $comment->where('is_reply', STATUS_INACTIVE);
            if (isset($extra['limit']) && $extra['limit'] > 0)
                $mainComment = $mainComment->slice(0, $extra['limit']);

            $commentData = [];
            foreach ($mainComment as $com) {
                $data = [
                    'id' => $com->to,
                    'name' => $com->name,
                    'email' => $com->email,
                    'website' => $com->website,
                    'message' => $com->message,
                    'post_id' => ($com->post_id),
                    'replys' => [],
                ];
                foreach ($comment as $sub) {
                    if (
                        $sub->is_reply &&
                        $sub->reply_to == $com->id &&
                        $sub->post_id == $com->post_id
                    ) {
                        $data['replys'][] = [
                            'id' => $sub->to,
                            'name' => $sub->name,
                            'email' => $sub->email,
                            'website' => $sub->website,
                            'reply_to' => $com->to,
                            'message' => $sub->message,
                            //'post_id' => encrypt($sub->post_id),
                            // 'to' => $sub->to ?? null,
                        ];
                    }
                }
                $commentData[] = $data;
            }
            return responseData(true, __('Comment get successfully'), $commentData);
        } catch (\Exception $e) {
            storeException('getComment repo', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function getPendingBlogComment()
    {
        try {
            $data = BlogComment::where('status', STATUS_PENDING)->get();
            return responseData(true, __('Comment get successfully'), $data);
        } catch (\Exception $e) {
            storeException('repo getPendingComment', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }

    }
    public function getPendingNewsComment()
    {
        try {
            $data = NewsComment::where('status', STATUS_PENDING)->get();
            return responseData(true, __('Comment get successfully'), $data);
        } catch (\Exception $e) {
            storeException('repo getPendingComment', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function blogCommentList($slug)
    {
        try {
            $data = BlogComment::where('post_id', $slug)->get();
            return responseData(true, __('Comment get successfully'), $data);
        } catch (\Exception $e) {
            storeException('repo getPendingComment', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }
    public function newsCommentList($slug)
    {
        try {
            $data = NewsComment::where('post_id', $slug)->get();
            return responseData(true, __('Comment get successfully'), $data);
        } catch (\Exception $e) {
            storeException('repo getPendingComment', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function acceptComment($id, $model)
    {
        try {
            $comment = $model::where('id', $id)->first();
            $comment->update(['status' => STATUS_ACCEPTED]);
            return responseData(true, __('Comment accepted successfully'));
        } catch (\Exception $e) {
            storeException('repo acceptComment', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function deleteComment($slug, $model)
    {
        try {
            $comment = $model::findOrFail($slug);
            $comment->delete();
            return responseData(true, __('Comment deleted successfully'));
        } catch (\Exception $e) {
            storeException('repo deleteComment', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }
}