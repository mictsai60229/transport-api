<?php

namespace App\Formatters;

use Illuminate\Support\Facades\Validator;
use App\Formatters\BaseFormatter;


class ProductFormatter extends BaseFormatter{

    public $_name = "Product";

    protected $validationRules = [
        "_id" => "required",
        "prod_mid" => "nullable|numeric",
        "prod_oid" => "nullable|numeric",
        "url_total_scores" => "nullable|integer",
        "url_count_rec" => "nullable|integer",
        "url_avg_scores" => "nullable|numeric",
        "order_num" => "nullable|integer",
        "schedule_time_minutes" => "nullable|integer",
        "confirm_hour" =>  "nullable|integer",
        "immediately_use" => "nullable|boolean",
        "immediately_deliver" => "nullable|boolean",
        "immediately_confirm" => "nullable|boolean",
        "free_refund_policy" => "nullable|boolean",
        "free_refund_before_day" => "nullable|integer",
        "category" => "nullable",
        "bd_tag" => "nullable",
        "promo_tag" => "nullable",
        "voucher_type" => "nullable",
        "payment_invoice_type" => "nullable",
        "is_tourism_product" => "nullable|boolean",
        "readable_url" => "nullable",
        "areas" => "nullable",
        "countries.*" => "regex:/^A\d{2}\-\d{3}$/",
        "cities.*" => "regex:/^A\d{2}\-\d{3}\-\d{5}$/",
        "receive_countries" => "nullable",
        "receive_cities" => "nullable",
        "tags" => "nullable",
        "sub_tags" => "nullable",
        "sub_tag_count" => "nullable|integer",
        "guide_lang" => "nullable",
        "theme_code" => "nullable",
        "first_created_date" => "nullable",
        "sale_dates" => "nullable",
        "sale_status" => "nullable|integer",
        "experience_locations.*" => "nullable|numeric",
        "language.*.image_url_list.*" => "nullable",
        "language.*.translate" => "nullable",
        "language.*.name" => "nullable",
        "language.*.introduction" => "nullable",
        "language.*.country_text" => "nullable",
        "language.*.city_text" => "nullable",
        "language.*.keywords" => "nullable",
        "language.*.ngram" => "nullable",
        "locale.*.market_search" => "nullable|boolean",
        "locale.*.market_purchase_type" => "nullable",
        "locale.*.market_purchase_date" => "nullable",
        "locale.*.order_num" => "nullable|integer",
        "locale.*.sort_score" => "nullable|numeric",
        "locale.*.short_term" => "nullable|integer"
    ];

    protected $transformFunctions = [];
}