<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Traits\ApiResponse;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index()
    {
//        auth()->user()->unreadNotifications->markAsRead();
        $notifications = auth()->user()->notifications()->paginate(request()->query('limit', 10));
        $data = NotificationResource::collection($notifications);
        return $this->responseOk(__('lang.success'), $data);
    }

    public function read(NotificationRequest $request)
    {
        $notification = auth()->user()->notifications()->where('id', $request->notification_id)->first();
        $notification->markAsRead();
        return $this->responseCreated(__('lang.success'));
    }

    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return $this->responseCreated(__('lang.success'));
    }

    public function delete(NotificationRequest $request)
    {
        $notification = auth()->user()->notifications()->where('id', $request->notification_id)->first();
        $notification->delete();
        return $this->responseCreated(__('lang.success'));
    }

    public function unreadNotificationCount()
    {
        $data['count'] = auth()->user()->unreadNotifications->count();
        return $this->responseOk(__('lang.success'), $data);
    }
}
