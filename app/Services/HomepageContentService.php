<?php
namespace App\Services;

class HomepageContentService
{
    public function defaults(): array
    {
        return [
            'seo_title'=>'বাগানের প্রয়োজনীয় সবকিছু','seo_description'=>'জৈব সার, গ্রো ব্যাগ, কোকো পিট ও বাগানের প্রয়োজনীয় বিশ্বস্ত পণ্য সারাদেশে ডেলিভারি।',
            'hero_enabled'=>'1','hero_image'=>'','hero_kicker'=>'বিশ্বস্ত কৃষি ও বাগান পণ্য','hero_heading'=>'আপনার বাগানের প্রয়োজনীয় সবকিছু এক জায়গায়','hero_subheading'=>'জৈব সার • গ্রো ব্যাগ • কোকো পিট • মালচিং পেপার • বাগান উপকরণ','hero_primary_text'=>'পণ্য দেখুন','hero_secondary_text'=>'গার্ডেনিং প্যাক দেখুন',
            'trust_enabled'=>'1','trust_1'=>'সারা বাংলাদেশে ডেলিভারি','trust_2'=>'Cash on Delivery','trust_3'=>'বাছাই করা গার্ডেনিং পণ্য','trust_4'=>'প্রয়োজনে ফোনে সহায়তা',
            'categories_enabled'=>'1','categories_kicker'=>'সহজে খুঁজুন','categories_heading'=>'পণ্যের ক্যাটাগরি',
            'products_enabled'=>'1','products_kicker'=>'আপনার পছন্দের','products_heading'=>'জনপ্রিয় পণ্য',
            'packages_enabled'=>'1','packages_kicker'=>'একসাথে প্রয়োজনীয় সবকিছু','packages_heading'=>'গার্ডেনিং প্যাক','packages_subheading'=>'চাষ শুরু করার প্রয়োজনীয় পণ্য একসাথে',
            'guides_enabled'=>'1','guides_kicker'=>'নতুনদের জন্য','guides_heading'=>'কীভাবে ব্যবহার করবেন?','guide_1_title'=>'টমেটো চাষের গাইড','guide_1_description'=>'সঠিক মাটি, বীজ ও পরিচর্যার সহজ নির্দেশনা।','guide_2_title'=>'Grow Bag প্রস্তুত করার নিয়ম','guide_2_description'=>'ছাদ বা বারান্দায় চাষ শুরুর প্রস্তুতি।','guide_3_title'=>'জৈব সার ব্যবহারের নিয়ম','guide_3_description'=>'গাছের ধরন অনুযায়ী নিরাপদ প্রয়োগ।','guides_badge'=>'শীঘ্রই আসছে',
            'support_enabled'=>'1','support_heading'=>'বাগান নিয়ে সাহায্য প্রয়োজন?','support_subheading'=>'Taharat Agro-এর সাথে যোগাযোগ করুন।','support_call_text'=>'কল করুন','support_facebook_text'=>'Facebook',
        ];
    }
    public function all(): array{return array_replace($this->defaults(),(new SettingService())->all('homepage'));}
    public function set(array $values): void{(new SettingService())->setMany('homepage',$values);}
}
