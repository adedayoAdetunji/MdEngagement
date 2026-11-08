<?php

LtRoute::post('/engagement/enquiries', 'MdEngagement@TbEngagementEnquiryController@store')
    ->name('engagement.enquiries.store');

LtRoute::post('/engagement/subscribers', 'MdEngagement@TbEngagementSubscriberController@store')
    ->name('engagement.subscribers.store');

LtRoute::post('/engagement/unsubscribe', 'MdEngagement@TbEngagementSubscriberController@unsubscribe')
    ->name('engagement.subscribers.unsubscribe');

LtRoute::get('/engagement/admin/enquiries', 'MdEngagement@TbEngagementEnquiryController@index')
    ->name('admin.engagement.enquiries.index');

LtRoute::patch('/engagement/admin/enquiries/status', 'MdEngagement@TbEngagementEnquiryController@update')
    ->name('admin.engagement.enquiries.update');

LtRoute::get('/engagement/admin/subscribers', 'MdEngagement@TbEngagementSubscriberController@index')
    ->name('admin.engagement.subscribers.index');

