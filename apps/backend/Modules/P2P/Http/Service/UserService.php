<?php
namespace Modules\P2P\Http\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Modules\P2P\Entities\POrder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function userCenter()
    {
        try{
            $data = [];
            $user = authUser_p2p();
            $orders = POrder::where(fn($q)=>$q->where('seller_id', $user->id)->orWhere('buyer_id', $user->id));
            $getUserFeedback = getUserFeedback($user,$orders);
            $getUserOrderInfo = userTradeInfo($user,$orders);

            $data['total_trade'] = $getUserOrderInfo['total_trade'];
            $data['total_success_trade'] = $getUserOrderInfo['total_success_trade'];
            $data['completion_rate_30d'] = $getUserOrderInfo['completion_rate_30d'];
            $data['first_order_at'] = $getUserOrderInfo['first_order_at'];
            $data['user_register_at'] = $getUserOrderInfo['user_register_at'];

            $data['feedback_list'] = $getUserFeedback['feedback_list'];
            $data['positive_feedback_list'] = $getUserFeedback['positive_feedback_list'];
            $data['negative_feedback_list'] = $getUserFeedback['negative_feedback_list'];
            $data['positive'] = $getUserFeedback['positive'];
            $data['negative'] = $getUserFeedback['negative'];
            $data['positive_feedback'] = $getUserFeedback['positive_feedback'];
            $data['total_feedback'] = $getUserFeedback['total_feedback'];

            $data['user'] = $user;
            $data['user']->photo = showUserImageP2P($user->id);

            return responseData(true, __("User center found successfully"),$data);
        }catch(\Exception $e){
            storeException("userCenter", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    public function userProfile($user)
    {
        try{
            $data = [];
            $orders = POrder::where(fn($q)=>$q->where('seller_id', $user->id)->orWhere('buyer_id', $user->id));
            $getUserFeedback = getUserFeedback($user,$orders);
            $getUserOrderInfo = userTradeInfo($user,$orders);

            $data['total_trade'] = $getUserOrderInfo['total_trade'];
            $data['total_success_trade'] = $getUserOrderInfo['total_success_trade'];
            $data['completion_rate_30d'] = $getUserOrderInfo['completion_rate_30d'];
            $data['first_order_at'] = $getUserOrderInfo['first_order_at'];
            $data['user_register_at'] = $getUserOrderInfo['user_register_at'];

            $data['feedback_list'] = $getUserFeedback['feedback_list'];
            $data['positive_feedback_list'] = $getUserFeedback['positive_feedback_list'];
            $data['negative_feedback_list'] = $getUserFeedback['negative_feedback_list'];
            $data['positive'] = $getUserFeedback['positive'];
            $data['negative'] = $getUserFeedback['negative'];
            $data['positive_feedback'] = $getUserFeedback['positive_feedback'];
            $data['total_feedback'] = $getUserFeedback['total_feedback'];

            $data['user'] = $user;
            $data['user']->photo = showUserImageP2P($user->id);

            return responseData(true, __("User details found successfully"),$data);
        } catch(\Exception $e) {
            storeException("userProfile", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}
