<?php

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Admin\Product;
use App\Models\Admin\Category;
use App\Models\Admin\Currency;
use App\Models\Admin\SubCategory;
use App\Models\Admin\ProductImage;
use App\Models\Admin\Brand;
use App\Models\Admin\Publisher;
use App\Models\Admin\Writer;
use App\Models\Admin\Subject;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Models\Admin\ProductOptionTopping;
use App\Models\Admin\ProductSize;
use App\Models\Admin\NavItem;
use App\Models\Admin\DelivaryCharge;
use App\Models\Admin\Coupon;
use App\Models\Admin\Area;
use App\Models\Admin\District;
use App\Models\Admin\DeliveryPercentage;
use Illuminate\Support\Facades\Http;
use App\Models\Booking;

function pendingBooking()
{
  $apiUrl = 'https://quickphonefixandmore.com/wp-json/jet-cct/booking_form_data';
  $response = Http::get($apiUrl);

  // Check if the response is successful
  if ($response->successful()) {
    $bookingData = $response->json(); // Decode the JSON data
  } else {
    $bookingData = []; // Handle error or fallback
  }

  $bookings = Booking::pluck('id')->toArray();

  $cnt = 0;
  foreach ($bookingData as $data) {
    if (!in_array($data['_ID'], $bookings)) $cnt++;
  }

  return $cnt;
}

function getProductImage($id)
{
  return ProductImage::where('product_id', $id)->get();
}
function getStatus()
{
  return [
    0 => 'Inactive',
    1 => 'Active',
  ];
}

function getAmountType()
{
  return [
    1 => 'Percentage',
    0 => 'Direct',
  ];
}

function getUser($id)
{
  return User::where('id', $id)->pluck('name')->first();
}
function getUserPhone($id)
{
  return User::where('id', $id)->pluck('phone')->first();
}

function orderStatuses()
{
  return [
    '1' => 'Pending',
    '2' => 'Processing',
    '3' => 'Shipped',
    '4' => 'Out for Delivery',
    '5' => 'Delivered',
    '6' => 'Canceled',
    '7' => 'Backordered',
    '8' => 'Returned',
    '9' => 'Refunded',
  ];
}


function orderStatusTitle()
{
  return [
    1 => 'অর্ডারটি কনফার্মেশনের জন্য অপেক্ষমাণ। শীঘ্রই আমাদের একজন প্রতিনিধি এসএমএস বা ফোন কলের মাধ্যমে অর্ডারটি কনফার্ম করবেন ইন-শা-আল্লাহ।',
    2 => 'অর্ডারটি প্রক্রিয়াকরণে রয়েছে এবং প্রস্তুতির কাজ চলছে।',
    3 => 'আপনার অর্ডারটি পাঠানো হয়েছে এবং এটি পথে রয়েছে। শীঘ্রই আপনি এটি পেয়ে যাবেন।',
    4 => 'অর্ডারটি ডেলিভারির জন্য রওনা হয়েছে। দয়া করে প্রস্তুত থাকুন।',
    5 => 'অর্ডারটি সফলভাবে ডেলিভারি করা হয়েছে। আমাদের সেবা ব্যবহারের জন্য ধন্যবাদ।',
    6 => 'দুঃখিত, আপনার অর্ডারটি বাতিল করা হয়েছে। কোনো প্রশ্ন থাকলে আমাদের সাথে যোগাযোগ করুন।',
    7 => 'এই পণ্যটি স্টকে নেই এবং ব্যাকঅর্ডার হিসাবে রাখা হয়েছে। স্টকে এলে শীঘ্রই আপনাকে জানানো হবে।',
    8 => 'আপনার অর্ডারটি ফেরত দেওয়া হয়েছে এবং প্রক্রিয়াকরণে রয়েছে। ধন্যবাদ।',
    9 => 'আপনার অর্থ ফেরত দেওয়া হয়েছে। দয়া করে ২-৩ কার্যদিবস অপেক্ষা করুন।',
  ];
}


function currecySymbleType()
{
  return [
    '1' => 'Prefix',
    '2' => 'Suffix',
  ];
}
function getCurrency()
{
  return Currency::where('status', '1')->pluck('symbol')->first();
}


function shceduleTypes()
{
  return [
    'Delivery' => 'Delivery',
    'Dining room and pick-up' => 'Dining room and pick-up',
  ];
}

function userTypes()
{
  return [
    '1' => 'Super Admin',
    '2' => 'Customer',
    '3' => 'Delivery Boy',
  ];
}

function getNotifications()
{
  $notifications = Notification::where('status', '1')->orderBy('created_at', 'DESC')->get();
  return $notifications;
}
function unSeenNotifications()
{
  $notifications = Notification::where('status', '1')->where('isSeen', '0')->get();
  return count($notifications);
}

function displayNotificationTime($timestamp)
{
  $time_ago = strtotime($timestamp);
  $current_time = time();
  $time_difference = $current_time - $time_ago;
  $minutes = round($time_difference / 60);
  $hours = round($time_difference / 3600);
  $seconds = round($time_difference);

  if ($seconds < 60) {
    if ($seconds <= 1) {
      return "1 second ago";
    } else {
      return "$seconds seconds ago";
    }
  } elseif ($minutes < 60) {
    if ($minutes <= 1) {
      return "1 minute ago";
    } else {
      return "$minutes minutes ago";
    }
  } elseif ($hours < 24) {
    if ($hours <= 1) {
      return "1 hour ago";
    } else {
      return "$hours hours ago";
    }
  } else {
    return date("d M \a\\t H:i", $time_ago);
  }
}

function sendEmployeeCredential($data)
{
  $data['email'] = "shawonmahmodul12@gmail.com";

  $companyName = 'Company Name';
  $companyEmail = 'shawonmahmodul12@gmail.com';

  Mail::send('emails.employee',  ['data' => $data], function ($m) use ($data, $companyEmail, $companyName) {
    $m->from($companyEmail, 'Credentials of ' . $companyName);
    $m->to($data['email'])->subject('HRIS Access Information');
  });
}

function getSelectedTopings($id)
{
  return ProductOptionTopping::join('topings', 'topings.id', '=', 'product_option_toppings.topping_id')->select('topings.*')->where('product_option_toppings.product_option_id', $id)->get();
}

function getHost()
{
  $host = request()->getHost();
  $host = str_replace('www.', '', $host);
  return $host;
}

function getRootURL()
{
  $currentUrl = request()->url();
  $parsed_url = parse_url($currentUrl);
  $host = $parsed_url['host'];
  $port = isset($parsed_url['port']) ? $parsed_url['port'] : null;

  $result = $host;
  if ($port !== null) {
    $result = $host . ':' . $port;
  }
  return $result;
}


function checkRole()
{
  $user = Auth::user();
  return $user->getRoleNames()['0'];
}

function return_library($object, $key_col, $value_col)
{
  $data = array();
  foreach ($object as $item)
    $data[$item->$key_col] = $item->$value_col;
  return $data;
}

function lib_all_category()
{
  return return_library(Category::where('status', '1')->get(), 'id', 'name');
}

function lib_category()
{
  return return_library(Category::where('for_book_or_product', '2')->where('status', '1')->get(), 'id', 'name');
}
function lib_book_category()
{
  return return_library(Category::where('for_book_or_product', '1')->where('status', '1')->get(), 'id', 'name');
}

function lib_brand()
{
  return return_library(Brand::where('status', '1')->get(), 'id', 'name');
}
function lib_publisher()
{
  return return_library(Publisher::where('status', '1')->get(), 'id', 'name');
}
function lib_writer()
{
  return return_library(Writer::where('status', '1')->get(), 'id', 'name');
}
function lib_subject()
{
  return return_library(Subject::where('status', '1')->get(), 'id', 'name');
}

function lib_serviceMan()
{
  return return_library(User::where('status', '1')->get(), 'id', 'name');
}

function lib_salesMan()
{
  return return_library(User::where('status', '1')->get(), 'id', 'name');
}

function country_codes()
{
  return [
    '+1' => ['flag' => '🇺🇸', 'code' => '+1', 'name' => 'United States'],
    '+44' => ['flag' => '🇬🇧', 'code' => '+44', 'name' => 'United Kingdom'],
    '+880' => ['flag' => '🇧🇩', 'code' => '+880', 'name' => 'Bangladesh'],
    '+91' => ['flag' => '🇮🇳', 'code' => '+91', 'name' => 'India'],
    '+92' => ['flag' => '🇵🇰', 'code' => '+92', 'name' => 'Pakistan'],
    '+93' => ['flag' => '🇦🇫', 'code' => '+93', 'name' => 'Afghanistan'],
    '+94' => ['flag' => '🇱🇰', 'code' => '+94', 'name' => 'Sri Lanka'],
    '+95' => ['flag' => '🇲🇲', 'code' => '+95', 'name' => 'Myanmar'],
    '+86' => ['flag' => '🇨🇳', 'code' => '+86', 'name' => 'China'],
    '+81' => ['flag' => '🇯🇵', 'code' => '+81', 'name' => 'Japan'],
    '+82' => ['flag' => '🇰🇷', 'code' => '+82', 'name' => 'South Korea'],
    '+971' => ['flag' => '🇦🇪', 'code' => '+971', 'name' => 'United Arab Emirates'],
    '+966' => ['flag' => '🇸🇦', 'code' => '+966', 'name' => 'Saudi Arabia'],
    '+20' => ['flag' => '🇪🇬', 'code' => '+20', 'name' => 'Egypt'],
    '+33' => ['flag' => '🇫🇷', 'code' => '+33', 'name' => 'France'],
    '+49' => ['flag' => '🇩🇪', 'code' => '+49', 'name' => 'Germany'],
    '+39' => ['flag' => '🇮🇹', 'code' => '+39', 'name' => 'Italy'],
    '+34' => ['flag' => '🇪🇸', 'code' => '+34', 'name' => 'Spain'],
    '+7' => ['flag' => '🇷🇺', 'code' => '+7', 'name' => 'Russia'],
    '+61' => ['flag' => '🇦🇺', 'code' => '+61', 'name' => 'Australia'],
    '+63' => ['flag' => '🇵🇭', 'code' => '+63', 'name' => 'Philippines'],
    '+234' => ['flag' => '🇳🇬', 'code' => '+234', 'name' => 'Nigeria'],
    '+55' => ['flag' => '🇧🇷', 'code' => '+55', 'name' => 'Brazil'],
    '+27' => ['flag' => '🇿🇦', 'code' => '+27', 'name' => 'South Africa'],
    '+62' => ['flag' => '🇮🇩', 'code' => '+62', 'name' => 'Indonesia'],
    '+60' => ['flag' => '🇲🇾', 'code' => '+60', 'name' => 'Malaysia'],
    '+64' => ['flag' => '🇳🇿', 'code' => '+64', 'name' => 'New Zealand'],
    '+212' => ['flag' => '🇲🇦', 'code' => '+212', 'name' => 'Morocco'],
    '+52' => ['flag' => '🇲🇽', 'code' => '+52', 'name' => 'Mexico'],
    '+356' => ['flag' => '🇲🇹', 'code' => '+356', 'name' => 'Malta'],
  ];
}

function get_names($data, $ids)
{
  if (getType($ids) != "array") {
    $ids = explode(',', $ids);
  }

  $str = [];
  foreach ($ids as $id) {
    $str[] = getArrayData($data, $id);
  }
  return implode(',', $str);
}

function getArrayData($datas, $key)
{
  $result = isset($datas[$key]) ? $datas[$key] : '';
  return $result;
}

function _print($data, $exit = 0)
{
  echo "<pre>";
  print_r($data);
  echo "</pre>";
  if (!$exit) exit;
}
function cartDetails()
{
  return Session::get('cart_details', []);
}
function removecartDetails()
{
  return Session::put('cart_details', []);
}
function getDeliveryTypeById($id)
{
  return DelivaryCharge::where('id', $id)->first();
}

function getSelectedDeliveryType()
{
  $details = cartDetails();
  $deliveryType = getDeliveryTypeById(isset($details['delivery_type']) ?  $details['delivery_type'] : null);
  $percent = deliveryChargeParcentage();
  $amount = $deliveryType ? $deliveryType->amount : 0;
  $data = [
    'id' => $deliveryType ? $deliveryType->id : null,
    'amount' => round($amount - ($percent / 100) * $amount),
  ];
  return $data;
}

function cartItems()
{
  return Session::get('cart', []);
}

function cartCount()
{
  $cart = cartItems();
  return count($cart);
}

function clearCart()
{
  Session::put('cart', []);
  Session::put('cart_details', []);
}

function getTotalcartValue()
{
  $cart = cartItems();
  $currectPrice = 0;
  foreach ($cart as $item) {
    $pro = Product::where('id', $item['product_id'])->first();
    $proSize = ProductSize::where('id', $item['size_id'])->first();
    if ($pro && !($pro->is_size == '1' && !$proSize) && (!$proSize || ($proSize && $pro->id == $proSize->product_id))) {
      if ($pro->is_size == '1' && $pro->size_wise_price == '1') {
        if (isOffer($proSize)) $currectPrice +=  $proSize->offer_price * $item['quantity'];
        else $currectPrice +=  $proSize->price * $item['quantity'];
      } else {
        if (isOffer($pro)) $currectPrice +=  $pro->offer_price * $item['quantity'];
        else $currectPrice +=  $pro->price * $item['quantity'];
      }
    }
  }

  return $currectPrice;
}


function befourShippingCharge()
{
  $total = getTotalcartValue();
  $couponDetails = getSelectedCoupon();
  $discount = 0;
  if ($couponDetails['response_code'] == '3') {
    if ($couponDetails['coupon']['discount_type'] == '1') {
      $discount = ($couponDetails['coupon']['discount'] / 100) * $total;
    } else {
      $discount = $couponDetails['coupon']['discount'];
    }
  }
  return $total - round($discount);
}

function getTotalcartValueWithAll()
{
  $afterDicount = befourShippingCharge();
  $deliveryType = getSelectedDeliveryType();

  return $afterDicount + $deliveryType['amount'];
}


function cartMiniView()
{
  return view('frontend.layouts.mini_cart');
}

function cartView()
{
  return view('frontend.layouts.cart');
}


function getBookType()
{
  return [
    '1' => 'Islamic',
    '2' => 'Genarel',
  ];
}

function getNotify($type)
{
  if ($type == 1) {
    $fmsg = 'Data Added Successfully';
  } elseif ($type == 2) {
    $fmsg = 'Data Updated Successfully';
  } elseif ($type == 3) {
    $fmsg = 'Data Deleted Successfully';
  } elseif ($type == 4) {
    $fmsg = 'Validation Error!';
  } elseif ($type == 5) {
    $fmsg = 'You Are Not Permitted';
  } elseif ($type == 6) {
    $fmsg = 'Provided Data Already Exists';
  } elseif ($type == 7) {
    $fmsg = 'No Information Found Matches Your Query';
  } elseif ($type == 8) {
    $fmsg = 'Data Not Found';
  } elseif ($type == 9) {
    $fmsg = 'Your given input is large than balance!';
  } elseif ($type == 10) {
    $fmsg = 'Operation Invalid!';
  } elseif ($type == 11) {
    $fmsg = 'Item add to cart success';
  } elseif ($type == 12) {
    $fmsg = 'No update required';
  } elseif ($type == 13) {
    $fmsg = 'Qty is within plus or minus 5% of purchase qty.';
  } else {
    $fmsg = 'Message Code Error';
  }
  return $fmsg;
}

function itemType()
{
  return [
    '1' => 'Category',
    '2' => 'Brand',
    '3' => 'Subject',
    '4' => 'Writer',
    '5' => 'Publisher',
    '6' => 'Menu',
  ];
}

function bookOrProduct()
{
  return [
    '1' => 'Book',
    '2' => 'Product',
  ];
}

function viewType()
{
  return [
    '1' => 'Carousel',
    '2' => 'Grid',
    '3' => 'Signle Carousel',
  ];
}

function getMenus()
{ //not use, just for see
  return [
    1 => 'Subject',
    2 => 'Writer',
    3 => 'Publisher',
    4 => 'Package',
    5 => 'Brand',
  ];
}

function isOffer($product)
{
  if (is_object($product)) {
    $product = $product->toArray();
  }
  if (is_array($product)) {
    $offerFrom = $product['offer_from'];
    $offerTo = $product['offer_to'];
    $offerPrice = $product['offer_price'];
    $currentDate = date('Y-m-d');
    if ($offerFrom <= $currentDate && $offerTo >= $currentDate && !empty($offerPrice)) {
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

function getOfferPrice($product)
{
  if (is_object($product)) {
    $product = $product->toArray();
  }
  if (is_array($product)) {
    return isOffer() && isset($product['offer_price']) ?: 0;
  } else {
    return 0;
  }
}

function getMinAmountSize($product_id)
{
  return $size = ProductSize::where('product_id', $product_id)
    ->orderBy('price', 'asc')
    ->first();
}
function getMaxAmountSize($product_id)
{
  return $size = ProductSize::where('product_id', $product_id)
    ->orderBy('price', 'desc')
    ->first();
}
function isDivisor($value)
{
  return $value == 0 ? 1 : $value;
}

function getOfferParcent($product)
{
  if (is_object($product)) {
    $product = $product->toArray();
  }
  if (is_array($product)) {
    $price = isset($product['price']) ? $product['price'] : 0;
    $offer_price = isset($product['offer_price']) ? $product['offer_price']  : 0;
    $parcent = (($price - $offer_price) / isDivisor($price)) * 100;
    return _numFormate($parcent, 1);
  } else {
    return 0;
  }
}

function _numFormate($number, $digit = 2)
{
  return number_format($number, $digit);
}

function countWords($string)
{
  preg_match_all('/\p{L}+/u', $string, $matches);
  return count($matches[0]);
}

function limitWords($string, $limit)
{
  $words = preg_split('/\s+/u', $string);
  return implode(' ', array_slice($words, 0, $limit));
}

function getArrayCond($column, $ids)
{
  if (!is_array($ids)) {
    $ids = $ids->toArray();
  }
  $condition = implode(' OR ', array_map(function ($id) use ($column) {
    return "FIND_IN_SET(" . intval($id) . ", $column)";
  }, $ids));

  return "$condition";
}


function getSortType()
{
  return [
    1 => 'More relevant',
    2 => 'Most Popular',
    3 => 'Discount - Low to High',
    4 => 'Discount - High to Low',
    5 => 'Price - Low to High',
    6 => 'Price - High to Low',
  ];
}

function navItems()
{
  return $navItems = NavItem::where('status', '1')
    ->select('nav_items.*')
    ->orderBy('order_by', 'asc')
    ->get();
}

function getDeliveryCharge()
{
  $charges = DelivaryCharge::where('status', '1')->get();
  $percent = deliveryChargeParcentage();
  foreach ($charges as $charge) {
    $charge->amount -= round(($percent / 100) * $charge->amount);
  }

  return $charges;
}

function lib_deliveryCharge()
{
  return return_library(DelivaryCharge::where('status', '1')->get(), 'id', 'name');
}

function isPermitedForOrder()
{
  $deliveryType = getSelectedDeliveryType();
  $isPermited = false;
  if ($deliveryType['id'] > 0) $isPermited = true;
  return $isPermited;
}

function checkCoupon($coupon)
{
  if (!(is_object($coupon) || is_array($coupon))) {
    $coupon = Coupon::where('id', $coupon)->first();
    if (!$coupon) {
      return ['response_code' => 1,];
    }
  }
  if (is_object($coupon)) {
    $coupon = $coupon->toArray();
  }
  if (!count($coupon)) {
    return ['response_code' => 1,];
  }

  $expiresAtTimestamp = strtotime($coupon['expires_at']);
  $currentTimestamp = time();
  if ($expiresAtTimestamp > $currentTimestamp && $coupon['status'] == '1') {
    return [
      'response_code' => 3,
      'coupon' => $coupon,
    ];
  } else {
    return ['response_code' => 2,];
  }
}

function getCouponDetails($coupon)
{
  $coupon_code = $coupon;
  $coupons = Coupon::where('code', $coupon)->get();
  $response = 1;
  $coupon = null;
  foreach ($coupons as $item) {
    $data = checkCoupon($item);
    if ($data['response_code'] >= $response) {
      $response = $data['response_code'];
      if ($response == 3) {
        if (!$coupon) $coupon = $data['coupon'];
        else {
          if ($data['coupon']['discount'] > $coupon['discount']) {
            $coupon['discount'] = $data['coupon']['discount'];
          }
        }
      }
    }
  }
  return [
    'coupon_code' => $coupon_code,
    'response_code' => $response,
    'coupon' => $coupon,
  ];
}

function getSelectedCoupon()
{
  $details = cartDetails();
  $couponDetails = getCouponDetails(isset($details['coupon']) ?  $details['coupon'] : null);
  return $couponDetails;
}


function generateCheckOutPage()
{
  $user = auth()->user();
  $districts = District::where('status', '1')->get();
  $areas = Area::where('status', '1')->get();
  return view('frontend.layouts.checkout_page', compact('user', 'districts', 'areas'));
}

function getPaymentMethods()
{
  return [
    1 => [
      'id' => '1',
      'name' => 'ক্যাশ অন ডেলিভারি',
      'image' => '',
      'remarks' => 'পণ্য ডেলিভারির পরে নগদ টাকা দিতে হবে।',
      'payment_type' => 'offline',
    ],
    2 => [
      'id' => '2',
      'name' => 'বিকাশ',
      'image' => 'bKash.webp',
      'remarks' => 'বিকাশের মাধ্যমে পেমেন্ট করুন।',
      'payment_type' => 'online',
    ],
    3 => [
      'id' => '3',
      'name' => 'রকেট',
      'image' => 'rocket.webp',
      'remarks' => 'রকেটের মাধ্যমে পেমেন্ট করুন।',
      'payment_type' => 'online',
    ],
    4 => [
      'id' => '4',
      'name' => 'নগদ',
      'image' => 'nagad.webp',
      'remarks' => 'নগদের মাধ্যমে পেমেন্ট করুন।',
      'payment_type' => 'online',
    ],
    // 5 => [
    //   'id' => '5',
    //   'name' => 'ভিসা / মাস্টারকার্ড ',
    //   'image' => 'sslcz-verified.webp',
    //   'remarks' => 'ভিসা / মাস্টারকার্ড দিয়ে পেমেন্ট করুন।',
    //   'payment_type' => 'online',
    // ],
  ];
}

function lib_districts()
{
  return return_library(District::where('status', '1')->get(), 'id', 'name');
}
function lib_areas()
{
  return return_library(Area::where('status', '1')->get(), 'id', 'name');
}

function deliveryChargeParcentage()
{
  $providedValue = befourShippingCharge();
  $closestMatch = DeliveryPercentage::where('min_amount', '<=', $providedValue)
    ->where('status', '1')
    ->orderBy('min_amount', 'desc')
    ->first();
  return $closestMatch ? $closestMatch->charge_percentage : 0;
}

function targetedDeliveryChargeParcentage()
{
  $providedValue = befourShippingCharge();
  $closestMatch = DeliveryPercentage::where('min_amount', '>', $providedValue)
    ->where('status', '1')
    ->orderBy('min_amount', 'asc')
    ->first();
  return $closestMatch;
}

function paymentMethods()
{
  return [
    '1' => 'Cash Payment',
    '2' => 'Card Payment',
    '3' => 'Other Payment',
  ];
}


function attendanceStatus()
{
  return [
    '1' => 'Present',
    '2' => 'Absent',
    '3' => 'Leave',
  ];
}



function allDistrict()
{
  return [
    1 => "ঢাকা",
    2 => "ফরিদপুর",
    3 => "গাজীপুর",
    4 => "গোপালগঞ্জ",
    5 => "কিশোরগঞ্জ",
    6 => "মাদারীপুর",
    7 => "মানিকগঞ্জ",
    8 => "মুন্সিগঞ্জ",
    9 => "নারায়ণগঞ্জ",
    10 => "নরসিংদী",
    11 => "রাজবাড়ী",
    12 => "শরীয়তপুর",
    13 => "টাঙ্গাইল",
    14 => "বগুড়া",
    15 => "জয়পুরহাট",
    16 => "নওগাঁ",
    17 => "নাটোর",
    18 => "চাঁপাইনবাবগঞ্জ",
    19 => "পাবনা",
    20 => "রাজশাহী",
    21 => "সিরাজগঞ্জ",
    22 => "দিনাজপুর",
    23 => "গাইবান্ধা",
    24 => "কুড়িগ্রাম",
    25 => "লালমনিরহাট",
    26 => "নীলফামারী",
    27 => "পঞ্চগড়",
    28 => "রংপুর",
    29 => "ঠাকুরগাঁও",
    30 => "ব্রাহ্মণবাড়িয়া",
    31 => "চাঁদপুর",
    32 => "কুমিল্লা",
    33 => "কক্সবাজার",
    34 => "ফেনী",
    35 => "খাগড়াছড়ি",
    36 => "লক্ষ্মীপুর",
    37 => "নোয়াখালী",
    38 => "রাঙামাটি",
    39 => "বান্দরবান",
    40 => "চট্টগ্রাম",
    41 => "হবিগঞ্জ",
    42 => "মৌলভীবাজার",
    43 => "সুনামগঞ্জ",
    44 => "সিলেট",
    45 => "বাগেরহাট",
    46 => "চুয়াডাঙ্গা",
    47 => "যশোর",
    48 => "ঝিনাইদহ",
    49 => "খুলনা",
    50 => "কুষ্টিয়া",
    51 => "মাগুরা",
    52 => "মেহেরপুর",
    53 => "নড়াইল",
    54 => "সাতক্ষীরা",
    55 => "বরগুনা",
    56 => "বরিশাল",
    57 => "ভোলা",
    58 => "ঝালকাঠি",
    59 => "পটুয়াখালী",
    60 => "পিরোজপুর",
    61 => "ময়মনসিংহ",
    62 => "জামালপুর",
    63 => "নেত্রকোণা",
    64 => "শেরপুর"
  ];
}

function districtWiseArea()
{
  return [
    1 => [
      1 => "আগারগাঁও",
      2 => "আজিমপুর",
      3 => "আদাবর",
      4 => "ইব্রাহিমপুর",
      5 => "ইসলামপুর",
      6 => "ইস্কাটন",
      7 => "উত্তর খান",
      8 => "উত্তরা",
      9 => "এ্যালিফেন্ট রোড",
      10 => "ওয়ারী",
      11 => "কদমতলী",
      12 => "কমলাপুর",
      13 => "কলাবাগান",
      14 => "কল্যাণপুর",
      15 => "কাওরানবাজার",
      16 => "কাকরাইল",
      17 => "কাজীপাড়া",
      18 => "কাঠালবাগান",
      19 => "কাফরুল",
      20 => "কামরঙ্গীরচর",
      21 => "কেরানীগঞ্জ",
      22 => "কোতয়ালী",
      23 => "ক্যান্টনমেন্ট",
      24 => "খিলখেত",
      25 => "খিলগাঁও",
      26 => "গাবতলী",
      27 => "গুলশান-১",
      28 => "গুলশান-২",
      29 => "গুলিস্থান",
      30 => "গেন্ডারিয়া",
      31 => "গ্রীন রোড",
      32 => "চকবাজার",
      33 => "জিগাতলা",
      34 => "জুরাইন",
      35 => "টিকাটুলি",
      36 => "ডিইউ ক্যাম্পাস",
      37 => "ডেমরা",
      38 => "তুরাগ",
      39 => "তেজকুনিপাড়া",
      40 => "তেজগাঁও",
      41 => "দক্ষিণ খান",
      42 => "দয়াগঞ্জ",
      43 => "দিয়াবাড়ী",
      44 => "দোহার",
      45 => "ধানমন্ডি",
      46 => "ধামরাই",
      47 => "নবাবগঞ্জ",
      48 => "নয়া পল্টন",
      49 => "নাখালপাড়া",
      50 => "নারিন্দা",
      51 => "নিউ ইস্কাটন",
      52 => "নিউ মার্কেট",
      53 => "নিকুঞ্জ",
      54 => "নিকেতন",
      55 => "নীলক্ষেত",
      56 => "পলাশী",
      57 => "পল্লবী",
      58 => "পান্থপথ",
      59 => "পুরানা পল্টন",
      60 => "পূর্বাচল",
      61 => "পোস্তগোলা",
      62 => "ফার্মগেট",
      63 => "বংশাল",
      64 => "বকশীবাজার",
      65 => "বনশ্রী",
      66 => "বনানী",
      67 => "বনানী ডিওএইচএস",
      68 => "বসুন্ধরা",
      69 => "বাংলাবাজার",
      70 => "বাংলামটর",
      71 => "বাড্ডা",
      72 => "বারিধারা",
      73 => "বারিধারা ডিওএইচএস",
      74 => "বাসাবো",
      75 => "বিমানবন্দর থানা",
      76 => "বুয়েট ক্যাম্পাস",
      77 => "মগবাজার",
      78 => "মতিঝিল",
      79 => "মধ্য বাড্ডা",
      80 => "মধ্য ভাটারা",
      81 => "মহাখালী",
      82 => "মহাখালী ডিওএইচএস",
      83 => "মালিবাগ",
      84 => "মিরপুর",
      85 => "মিরপুর ডিওএইচএস",
      86 => "মিরপুর-১",
      87 => "মিরপুর-১০",
      88 => "মিরপুর-১১",
      89 => "মিরপুর-১২",
      90 => "মিরপুর-২",
      91 => "মুগদা",
      92 => "মোহাম্মদপুর",
      93 => "যাত্রাবাড়ী",
      94 => "রমনা",
      95 => "রাজাবাজার",
      96 => "রাজারবাগ",
      97 => "রামপুরা",
      98 => "রায়েরবাজার",
      99 => "রূপনগর",
      100 => "লালবাগ",
      101 => "লালমাটিয়া",
      102 => "শান্তিনগর",
      103 => "শাহজাদপুর",
      104 => "শাহজানপুর",
      105 => "শাহবাগ",
      106 => "শিমরাইল",
      107 => "শুক্রাবাদ",
      108 => "শেরে বাংলা নগর",
      109 => "শ্যামপুর",
      110 => "শ্যামলী",
      111 => "সদরঘাট",
      112 => "সবুজবাগ",
      113 => "সাভার",
      114 => "সিদ্ধেশ্বরী",
      115 => "সূত্রাপুর",
      116 => "সেগুনবাগিচা",
      117 => "হাজারীবাগ",
      118 => "হাতিরপুল"
    ],
    2 => [1 => "চরভদ্রাসন", 2 => "ভাঙ্গা", 3 => "সোনাডাঙ্গী", 4 => "বাউফল", 5 => "রাজৈর"],
    3 => [1 => "গাজীপুর সদর", 2 => "কিরণপুর", 3 => "কালীগঞ্জ", 4 => "কাপাসিয়া", 5 => "সিটি কর্পোরেশন"],
    4 => [1 => "গোপালগঞ্জ সদর", 2 => "টুঙ্গিপাড়া", 3 => "কোটালীপাড়া", 4 => "মুকসুদপুর"],
    5 => [1 => "কিশোরগঞ্জ সদর", 2 => "ভৈরব", 3 => "হোসেনপুর", 4 => "এলেঙ্গা", 5 => "পাটি"],
    6 => [1 => "মাদারীপুর সদর", 2 => "শরীয়তপুর", 3 => "রাজৈর", 4 => "দক্ষিণাল", 5 => "রামনগর"],
    7 => [1 => "মানিকগঞ্জ সদর", 2 => "দৌলতপুর", 3 => "সাটুরিয়া", 4 => "ঘিওর", 5 => "হরিরামপুর"],
    8 => [1 => "মুন্সিগঞ্জ সদর", 2 => "লৌহজং", 3 => "শ্রীনগর", 4 => "মাওয়া", 5 => "রাজেন্দ্রপুর"],
    9 => [1 => "নারায়ণগঞ্জ সদর", 2 => "বন্দর", 3 => "রূপগঞ্জ", 4 => "আড়াইহাজার", 5 => "ফতুল্লা"],
    10 => [1 => "নরসিংদী সদর", 2 => "পলাশ", 3 => "রায়পুর", 4 => "মাধবদী", 5 => "শিবপুর"],
    11 => [1 => "রাজবাড়ী সদর", 2 => "গোয়ালন্দ", 3 => "বালিয়াকান্দি", 4 => "কালুখালী", 5 => "পাংশা"],
    12 => [1 => "শরীয়তপুর সদর", 2 => "গোবিন্দপুর", 3 => "বাউফল", 4 => "পালং", 5 => "গোসাইগাঁও"],
    13 => [1 => "টাঙ্গাইল সদর", 2 => "বাসাইল", 3 => "মধুপুর", 4 => "নাগরপুর", 5 => "দেলদুয়ার"],
    14 => [1 => "বগুড়া সদর", 2 => "কাহালু", 3 => "সোনাতলা", 4 => "শিবগঞ্জ", 5 => "গাবতলী"],
    15 => [1 => "জয়পুরহাট সদর", 2 => "কাচারি", 3 => "কালাই", 4 => "পাঁচবিবি", 5 => "মাহমুদপুর"],
    16 => [1 => "নওগাঁ সদর", 2 => "রাণীনগর", 3 => "আত্রাই", 4 => "বড়াইগ্রাম", 5 => "পোরশা"],
    17 => [1 => "নাটোর সদর", 2 => "বড়াইগ্রাম", 3 => "গুরুদাসপুর", 4 => "সিংড়া", 5 => "লালপুর"],
    18 => [1 => "চাঁপাইনবাবগঞ্জ সদর", 2 => "গোমস্তাপুর", 3 => "নাচোল", 4 => "ভোলাহাট", 5 => "শিবগঞ্জ"],
    19 => [1 => "পাবনা সদর", 2 => "ঈশ্বরদী", 3 => "চাটমোহর", 4 => "সাঁথিয়া", 5 => "বাঘাইছড়া"],
    20 => [1 => "রাজশাহী সদর", 2 => "বাগমারা", 3 => "পবা", 4 => "চারঘাট", 5 => "কাজীপুর"],
    21 => [1 => "সিরাজগঞ্জ সদর", 2 => "উল্লাপাড়া", 3 => "চান্দাইকোনা", 4 => "কাজিপুর", 5 => "শাহজাদপুর"],
    22 => [1 => "দিনাজপুর সদর", 2 => "বীরগঞ্জ", 3 => "খোরবানি", 4 => "নন্দীগ্রাম", 5 => "রানীশংকর"],
    23 => [1 => "গাইবান্ধা সদর", 2 => "ফুলছড়ি", 3 => "সাঘাটা", 4 => "পলাশবাড়ী", 5 => "নামাহ"],
    24 => [1 => "কুড়িগ্রাম সদর", 2 => "রাজীবপুর", 3 => "রাজারহাট", 4 => "ফুলবাড়ি", 5 => "অলিপুর"],
    25 => [1 => "লালমনিরহাট সদর", 2 => "কালীগঞ্জ", 3 => "পাটগ্রাম", 4 => "আটরা", 5 => "রামডাঙ্গা"],
    26 => [1 => "নীলফামারী সদর", 2 => "ডোমার", 3 => "জলঢাকা", 4 => "পার্বতীপুর", 5 => "চিলমারী"],
    27 => [1 => "পঞ্চগড় সদর", 2 => "আটোয়ারী", 3 => "বদরগঞ্জ", 4 => "ফাল্গুনী", 5 => "গাজীপুর"],
    28 => [1 => "রংপুর সদর", 2 => "পীরগঞ্জ", 3 => "ফুলবাড়ী", 4 => "কাউনিয়া", 5 => "মিঠাপুকুর"],
    29 => [1 => "ঠাকুরগাঁও সদর", 2 => "বালিয়া", 3 => "পীরগঞ্জ", 4 => "রাণীশংকর", 5 => "আদমপুর"],
    30 => [1 => "ব্রাহ্মণবাড়িয়া সদর", 2 => "কসবা", 3 => "নাসিরনগর", 4 => "সরাইল", 5 => "কুমিল্লা"],
    31 => [1 => "চাঁদপুর সদর", 2 => "হাইমচর", 3 => "মতলব", 4 => "শাহরাস্তি", 5 => "কচুয়া"],
    32 => [1 => "কুমিল্লা সদর", 2 => "লাকসাম", 3 => "মনোহরগঞ্জ", 4 => "চান্দিনা", 5 => "রামগঞ্জ"],
    33 => [1 => "কক্সবাজার সদর", 2 => "উখিয়া", 3 => "চকরিয়া", 4 => "টেকনাফ", 5 => "পেকুয়া"],
    34 => [1 => "ফেনী সদর", 2 => "পরশুরাম", 3 => "চট্টগ্রাম", 4 => "দাগনভূঞা", 5 => "কুমিল্লা"],
    35 => [1 => "খাগড়াছড়ি সদর", 2 => "মাটিরাঙা", 3 => "লক্ষীছড়ি", 4 => "বিলাইছড়ি", 5 => "কুতুবদিয়া"],
    36 => [1 => "লক্ষ্মীপুর সদর", 2 => "রামগঞ্জ", 3 => "কমলনগর", 4 => "হাজারী"],
    37 => [1 => "নোয়াখালী সদর", 2 => "ভালুকা", 3 => "সেনবাগ", 4 => "চর জব্বার", 5 => "কোম্পানীগঞ্জ"],
    38 => [1 => "রাঙামাটি সদর", 2 => "কাপ্তাই", 3 => "বাঘাইছড়া", 4 => "জুরাছড়ি"],
    39 => [1 => "বান্দরবান সদর", 2 => "থানচি", 3 => "রোয়াংছড়ি", 4 => "লোহাগাড়ি", 5 => "পেকুয়া"],
    40 => [1 => "চট্টগ্রাম সদর", 2 => "সীতাকুণ্ড", 3 => "ফটিকছড়ি", 4 => "পটিয়া", 5 => "কর্ণফুলী"],
    41 => [1 => "হবিগঞ্জ সদর", 2 => "বাহুবল", 3 => "চুনারুঘাট", 4 => "মাধবপুর"],
    42 => [1 => "মৌলভীবাজার সদর", 2 => "কুলাউড়া", 3 => "জুড়ী", 4 => "বড়লেখা"],
    43 => [1 => "সিলেট সদর", 2 => "গোলাপগঞ্জ", 3 => "বিশ্বনাথ", 4 => "ফেঞ্চুগঞ্জ"],
    44 => [1 => "কিশোরগঞ্জ", 2 => "ফেঞ্চুগঞ্জ", 3 => "সিলেট", 4 => "কুড়িগ্রাম"],
    45 => [1 => "বাগেরহাট সদর", 2 => "মোংলা", 3 => "রামপাল", 4 => "ফকিরহাট", 5 => "শরণখোলা"],
    46 => [1 => "চুয়াডাঙ্গা সদর", 2 => "আলমডাঙ্গা", 3 => "দর্শনা", 4 => "বেলকুচি"],
    47 => [1 => "যশোর সদর", 2 => "কেশবপুর", 3 => "মণিরামপুর", 4 => "বাঘারপাড়া", 5 => "শ্যামপুর"],
    48 => [1 => "ঝিনাইদহ সদর", 2 => "হরিণাকুন্ডু", 3 => "মহেশপুর", 4 => "কালীগঞ্জ", 5 => "বাঘা"],
    49 => [1 => "খুলনা সদর", 2 => "রূপসা", 3 => "কেসবাগ", 4 => "বাগেরহাট", 5 => "দুমকি"],
    50 => [1 => "কুষ্টিয়া সদর", 2 => "কুমারখালী", 3 => "ভেড়ামারা", 4 => "আটলেথি", 5 => "নগর পত্র"],
    51 => [1 => "মাগুরা সদর", 2 => "মোহাম্মদপুর", 3 => "শ্রীপুর", 4 => "মহম্মদপুর", 5 => "নামাজ গ্রাম"],
    52 => [1 => "মেহেরপুর সদর", 2 => "মুজিবনগর", 3 => "গাংনী", 4 => "চুড়ামনি", 5 => "ভেরী"],
    53 => [1 => "নড়াইল সদর", 2 => "লোহাগড়া", 3 => "কালিয়া", 4 => "অপরূপ", 5 => "মল্লিক"],
    54 => [1 => "সাতক্ষীরা সদর", 2 => "কালীগঞ্জ", 3 => "পাটকেলঘাটা", 4 => "শ্যামনগর", 5 => "আশাশুনি"],
    55 => [1 => "বরগুনা সদর", 2 => "আমতলী", 3 => "বেতাগী", 4 => "বাহেরচর", 5 => "গোবিন্দপুর"],
    56 => [1 => "বরিশাল সদর", 2 => "বানারিপাড়া", 3 => "চরসুন্দর", 4 => "মুলাদী", 5 => "কাউখালী"],
    57 => [1 => "ভোলা সদর", 2 => "মনপুরা", 3 => "বোরহানউদ্দিন", 4 => "দৌলতখান", 5 => "চরফ্যাশন"],
    58 => [1 => "ঝালকাঠি সদর", 2 => "শাহজাহান", 3 => "কাউখালী", 4 => "বাকেরগঞ্জ", 5 => "ঝালখাতি"],
    59 => [1 => "পটুয়াখালী সদর", 2 => "রাঙ্গাবালী", 3 => "মির্জাগঞ্জ", 4 => "বাউফল", 5 => "কুড়ালিয়া"],
    60 => [1 => "পিরোজপুর সদর", 2 => "মঠবাড়ী", 3 => "কালীগঞ্জ", 4 => "নাজিরপুর", 5 => "বাগমারা"],
    61 => [1 => "ময়মনসিংহ সদর", 2 => "ফুলপুর", 3 => "হালুয়াঘাট", 4 => "ঈশ্বরগঞ্জ", 5 => "গফরগাঁও"],
    62 => [1 => "জামালপুর সদর", 2 => "মেলান্দহ", 3 => "সরিষাবাড়ী", 4 => "ইটনা", 5 => "ভুলবাড়ী"],
    63 => [1 => "নেত্রকোণা সদর", 2 => "মদন", 3 => "আলমপুর", 4 => "শিমুলতলী", 5 => "দুর্গাপুর"],
    64 => [1 => "শেরপুর সদর", 2 => "নকলা", 3 => "শ্রীবরদী", 4 => "গৌরীপুর", 5 => "মধ্যপাড়া"]
  ];
}

function getBanglish($text)
{
  $map = [
    // Vowels
    'অ' => 'o',
    'আ' => 'a',
    'ই' => 'i',
    'ঈ' => 'i',
    'উ' => 'u',
    'ঊ' => 'u',
    'ঋ' => 'ri',
    'এ' => 'e',
    'ঐ' => 'oi',
    'ও' => 'o',
    'ঔ' => 'ou',

    // Consonants
    'ক' => 'k',
    'খ' => 'kh',
    'গ' => 'g',
    'ঘ' => 'gh',
    'ঙ' => 'ng',
    'চ' => 'ch',
    'ছ' => 'ch',
    'জ' => 'j',
    'ঝ' => 'jh',
    'ঞ' => 'n',
    'ট' => 't',
    'ঠ' => 'th',
    'ড' => 'd',
    'ঢ' => 'dh',
    'ণ' => 'n',
    'ত' => 't',
    'থ' => 'th',
    'দ' => 'd',
    'ধ' => 'dh',
    'ন' => 'n',
    'প' => 'p',
    'ফ' => 'f',
    'ব' => 'b',
    'ভ' => 'bh',
    'ম' => 'm',
    'য' => 'y',
    'র' => 'r',
    'ল' => 'l',
    'শ' => 'sh',
    'ষ' => 'sh',
    'স' => 's',
    'হ' => 'h',
    'ড়' => 'r',
    'ড়' => 'r',
    'ঢ়' => 'r',
    'য়' => 'y',
    'য়' => 'y',
    'ৎ' => 't',

    // Consonant conjuncts
    'ক্ষ' => 'kkh',
    'জ্ঞ' => 'gg',
    'ত্র' => 'tr',
    'দ্র' => 'dr',

    // Vowel diacritics (matra)
    'া' => 'a',
    'ি' => 'i',
    'ী' => 'i',
    'ু' => 'u',
    'ূ' => 'u',
    'ৃ' => 'ri',
    'ে' => 'e',
    'ৈ' => 'oi',
    'ো' => 'o',
    'ৌ' => 'ou',

    // Anusvara, Visarga, Chandrabindu, etc.
    'ং' => 'ng',
    'ঃ' => 'h',
    'ঁ' => 'n',

    // Additional consonants
    'ৎ' => 't',
    'ঃ' => 'h',
    'ঁ' => 'n',
    '্' => '',
  ];

  return strtr($text, $map);

  // $str = '';
  // for($i=0; $i<mb_strlen($text, 'UTF-8'); $i++){
  //     $cr = mb_substr($text, $i, 1, 'UTF-8');
  //     isset($map[$cr]) ?  $ecr = $map[$cr] : $ecr = $cr;

  //    // echo "$cr => $ecr <br>";

  //     $str .= $ecr;
  // }

  // return $str;

}
