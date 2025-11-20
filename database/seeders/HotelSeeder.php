<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\City;
use App\Models\File;
use App\Models\Hotel;
use App\Models\HotelType;
use App\Models\User;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::role('hotel')->get();
        $cities = City::get();

        $hotels = [
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق الأهرامات الذهبية',
                    'en' => 'Golden Pyramids Hotel',
                ],
                'latitude' => 29.9773,
                'longitude' => 31.1325,
                'address' => [
                    'ar' => 'القاهرة، مصر',
                    'en' => 'Cairo, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق فاخر بإطلالة رائعة على الأهرامات مع خدمات متميزة وغرف مجهزة بأحدث التقنيات.',
                    'en' => 'Luxury hotel with stunning views of the pyramids, excellent services, and rooms equipped with the latest technology.',
                ],
                'rating' => 5,
                'facilities' => [
                    'ar' => 'مسبح، مطعم، واي فاي مجاني، مواقف سيارات، صالة ألعاب رياضية',
                    'en' => 'Swimming pool, Restaurant, Free WiFi, Parking, Gym',
                ],
                'phone_key' => '+20',
                'phone' => '0123456789',
                'status' => Status::Active,
                'free_child_age' => 4,
                'adult_age' => 12,
                'first_child_price_percentage' => 50,
                'second_child_price_percentage' => 30,
                'third_child_price_percentage' => 20,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'منتجع البحر الأحمر',
                    'en' => 'Red Sea Resort',
                ],
                'latitude' => 27.2579,
                'longitude' => 33.8116,
                'phone_key' => '+20',
                'phone' => '0123456788',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'شرم الشيخ، مصر',
                    'en' => 'Sharm El Sheikh, Egypt',
                ],
                'description' => [
                    'ar' => 'منتجع شاطئي راقي مع شاطئ خاص وأنشطة مائية متنوعة وإطلالة ساحرة على البحر الأحمر.',
                    'en' => 'Upscale beach resort with private beach, various water activities, and charming views of the Red Sea.',
                ],
                'rating' => 5,
                'facilities' => [
                    'ar' => 'شاطئ خاص، غوص، سنوركلينج، سبا، مطاعم متعددة',
                    'en' => 'Private beach, Diving, Snorkeling, Spa, Multiple restaurants',
                ],
                'free_child_age' => 3,
                'adult_age' => 12,
                'first_child_price_percentage' => 40,
                'second_child_price_percentage' => 25,
                'third_child_price_percentage' => 15,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق الأقصر الملكي',
                    'en' => 'Luxor Royal Hotel',
                ],
                'latitude' => 25.6872,
                'longitude' => 32.6396,
                'phone_key' => '+20',
                'phone' => '0123456787',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'الأقصر، مصر',
                    'en' => 'Luxor, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق تاريخي يقع في قلب الأقصر بالقرب من المعابد الشهيرة مع خدمات راقية.',
                    'en' => 'Historic hotel located in the heart of Luxor near famous temples with premium services.',
                ],
                'rating' => 4,
                'facilities' => [
                    'ar' => 'إطلالة على النيل، مطعم، بار، حمام سباحة على السطح',
                    'en' => 'Nile view, Restaurant, Bar, Rooftop pool',
                ],
                'free_child_age' => 5,
                'adult_age' => 14,
                'first_child_price_percentage' => 60,
                'second_child_price_percentage' => 40,
                'third_child_price_percentage' => 25,
                'additional_child_price_percentage' => 15,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق النيل الكبير',
                    'en' => 'Grand Nile Hotel',
                ],
                'latitude' => 30.0444,
                'longitude' => 31.2357,
                'phone_key' => '+20',
                'phone' => '0123456786',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'القاهرة، مصر',
                    'en' => 'Cairo, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق خمس نجوم على ضفاف النيل يوفر إطلالات خلابة وخدمات استثنائية.',
                    'en' => 'Five-star hotel on the Nile banks offering breathtaking views and exceptional services.',
                ],
                'rating' => 5,
                'facilities' => [
                    'ar' => 'مسبح لا متناهي، سبا فاخر، مطاعم عالمية، قاعات مؤتمرات',
                    'en' => 'Infinity pool, Luxury spa, International restaurants, Conference halls',
                ],
                'free_child_age' => 4,
                'adult_age' => 12,
                'first_child_price_percentage' => 50,
                'second_child_price_percentage' => 30,
                'third_child_price_percentage' => 20,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'منتجع الجونة السياحي',
                    'en' => 'El Gouna Resort',
                ],
                'latitude' => 27.3958,
                'longitude' => 33.6753,
                'phone_key' => '+20',
                'phone' => '0123456785',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'الغردقة، مصر',
                    'en' => 'Hurghada, Egypt',
                ],
                'description' => [
                    'ar' => 'منتجع متكامل على البحر الأحمر مع ملعب جولف ومرسى لليخوت.',
                    'en' => 'Integrated resort on the Red Sea with golf course and yacht marina.',
                ],
                'rating' => 5,
                'facilities' => [
                    'ar' => 'ملعب جولف، مرسى، رياضات مائية، نادي للأطفال، مطاعم فاخرة',
                    'en' => 'Golf course, Marina, Water sports, Kids club, Fine dining',
                ],
                'free_child_age' => 5,
                'adult_age' => 13,
                'first_child_price_percentage' => 45,
                'second_child_price_percentage' => 30,
                'third_child_price_percentage' => 20,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق الإسكندرية البحري',
                    'en' => 'Alexandria Seaside Hotel',
                ],
                'latitude' => 31.2001,
                'longitude' => 29.9187,
                'phone_key' => '+20',
                'phone' => '0123456784',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'الإسكندرية، مصر',
                    'en' => 'Alexandria, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق راقي على كورنيش الإسكندرية مع إطلالة مباشرة على البحر المتوسط.',
                    'en' => 'Elegant hotel on Alexandria Corniche with direct Mediterranean Sea view.',
                ],
                'rating' => 4,
                'facilities' => [
                    'ar' => 'شاطئ خاص، مطعم بحري، سبا، واي فاي مجاني',
                    'en' => 'Private beach, Seafood restaurant, Spa, Free WiFi',
                ],
                'free_child_age' => 3,
                'adult_age' => 12,
                'first_child_price_percentage' => 40,
                'second_child_price_percentage' => 25,
                'third_child_price_percentage' => 15,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'منتجع سهل حشيش',
                    'en' => 'Sahl Hasheesh Resort',
                ],
                'latitude' => 26.8467,
                'longitude' => 33.9734,
                'phone_key' => '+20',
                'phone' => '0123456783',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'الغردقة، مصر',
                    'en' => 'Hurghada, Egypt',
                ],
                'description' => [
                    'ar' => 'منتجع فاخر بشاطئ رملي طويل وخدمات شاملة للعائلات.',
                    'en' => 'Luxury resort with long sandy beach and all-inclusive services for families.',
                ],
                'rating' => 5,
                'facilities' => [
                    'ar' => 'شامل كل شيء، نادي للأطفال، مسابح متعددة، ترفيه مسائي',
                    'en' => 'All-inclusive, Kids club, Multiple pools, Evening entertainment',
                ],
                'free_child_age' => 4,
                'adult_age' => 12,
                'first_child_price_percentage' => 50,
                'second_child_price_percentage' => 35,
                'third_child_price_percentage' => 25,
                'additional_child_price_percentage' => 15,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق أسوان النوبي',
                    'en' => 'Aswan Nubian Hotel',
                ],
                'latitude' => 24.0889,
                'longitude' => 32.8998,
                'phone_key' => '+20',
                'phone' => '0123456782',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'أسوان، مصر',
                    'en' => 'Aswan, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق بتصميم نوبي تقليدي على شاطئ النيل بالقرب من معبد فيلة.',
                    'en' => 'Hotel with traditional Nubian design on the Nile near Philae Temple.',
                ],
                'rating' => 4,
                'facilities' => [
                    'ar' => 'إطلالة على النيل، مطعم نوبي، جولات سياحية، تراس على السطح',
                    'en' => 'Nile view, Nubian restaurant, Tour arrangements, Rooftop terrace',
                ],
                'free_child_age' => 5,
                'adult_age' => 14,
                'first_child_price_percentage' => 55,
                'second_child_price_percentage' => 35,
                'third_child_price_percentage' => 20,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق دهب السياحي',
                    'en' => 'Dahab Tourist Hotel',
                ],
                'latitude' => 28.5096,
                'longitude' => 34.5165,
                'phone_key' => '+20',
                'phone' => '0123456781',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'دهب، جنوب سيناء، مصر',
                    'en' => 'Dahab, South Sinai, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق شاطئي هادئ مثالي لعشاق الغوص والاسترخاء.',
                    'en' => 'Peaceful beachfront hotel perfect for diving enthusiasts and relaxation.',
                ],
                'rating' => 4,
                'facilities' => [
                    'ar' => 'مركز غوص، شاطئ رملي، مطعم بدوي، رحلات سفاري',
                    'en' => 'Diving center, Sandy beach, Bedouin restaurant, Safari trips',
                ],
                'free_child_age' => 3,
                'adult_age' => 12,
                'first_child_price_percentage' => 35,
                'second_child_price_percentage' => 25,
                'third_child_price_percentage' => 15,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'منتجع مرسى علم المرجاني',
                    'en' => 'Marsa Alam Coral Resort',
                ],
                'latitude' => 25.0629,
                'longitude' => 34.8945,
                'phone_key' => '+20',
                'phone' => '0123456780',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'مرسى علم، البحر الأحمر، مصر',
                    'en' => 'Marsa Alam, Red Sea, Egypt',
                ],
                'description' => [
                    'ar' => 'منتجع على شاطئ بكر مع شعاب مرجانية رائعة ومركز غوص عالمي.',
                    'en' => 'Resort on pristine beach with amazing coral reefs and world-class diving center.',
                ],
                'rating' => 5,
                'facilities' => [
                    'ar' => 'مركز غوص معتمد، رياضات مائية، سبا، مطاعم متنوعة',
                    'en' => 'Certified diving center, Water sports, Spa, Diverse restaurants',
                ],
                'free_child_age' => 4,
                'adult_age' => 13,
                'first_child_price_percentage' => 45,
                'second_child_price_percentage' => 30,
                'third_child_price_percentage' => 20,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق الواحات البيضاء',
                    'en' => 'White Desert Oasis Hotel',
                ],
                'latitude' => 27.4833,
                'longitude' => 28.6167,
                'phone_key' => '+20',
                'phone' => '0123456779',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'الواحات البحرية، مصر',
                    'en' => 'Bahariya Oasis, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق فريد في الصحراء البيضاء يوفر تجربة سياحية استثنائية.',
                    'en' => 'Unique desert hotel in the White Desert offering exceptional tourist experience.',
                ],
                'rating' => 4,
                'facilities' => [
                    'ar' => 'رحلات سفاري، تخييم صحراوي، مطعم تقليدي، مرشدين سياحيين',
                    'en' => 'Safari trips, Desert camping, Traditional restaurant, Tour guides',
                ],
                'free_child_age' => 6,
                'adult_age' => 14,
                'first_child_price_percentage' => 50,
                'second_child_price_percentage' => 30,
                'third_child_price_percentage' => 20,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق طابا الملكي',
                    'en' => 'Taba Royal Hotel',
                ],
                'latitude' => 29.5871,
                'longitude' => 34.8953,
                'phone_key' => '+20',
                'phone' => '0123456778',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'طابا، جنوب سيناء، مصر',
                    'en' => 'Taba, South Sinai, Egypt',
                ],
                'description' => [
                    'ar' => 'منتجع ساحلي على خليج العقبة مع إطلالات بانورامية على أربع دول.',
                    'en' => 'Coastal resort on Gulf of Aqaba with panoramic views of four countries.',
                ],
                'rating' => 5,
                'facilities' => [
                    'ar' => 'كازينو، ملاعب تنس، مسابح متعددة، نادي صحي',
                    'en' => 'Casino, Tennis courts, Multiple pools, Health club',
                ],
                'free_child_age' => 4,
                'adult_age' => 12,
                'first_child_price_percentage' => 50,
                'second_child_price_percentage' => 30,
                'third_child_price_percentage' => 20,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق سيوة النخيل',
                    'en' => 'Siwa Palm Hotel',
                ],
                'latitude' => 29.2030,
                'longitude' => 25.5194,
                'phone_key' => '+20',
                'phone' => '0123456777',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'واحة سيوة، مصر',
                    'en' => 'Siwa Oasis, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق بيئي بين بساتين النخيل والعيون الطبيعية في واحة سيوة.',
                    'en' => 'Eco-friendly hotel among palm groves and natural springs in Siwa Oasis.',
                ],
                'rating' => 4,
                'facilities' => [
                    'ar' => 'حمامات طبيعية، رحلات صحراوية، مطعم عضوي، سبا طبيعي',
                    'en' => 'Natural baths, Desert tours, Organic restaurant, Natural spa',
                ],
                'free_child_age' => 5,
                'adult_age' => 13,
                'first_child_price_percentage' => 40,
                'second_child_price_percentage' => 25,
                'third_child_price_percentage' => 15,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق نويبع البدوي',
                    'en' => 'Nuweiba Bedouin Hotel',
                ],
                'latitude' => 29.0345,
                'longitude' => 34.6697,
                'phone_key' => '+20',
                'phone' => '0123456776',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'نويبع، جنوب سيناء، مصر',
                    'en' => 'Nuweiba, South Sinai, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق بأسلوب بدوي أصيل على شاطئ هادئ بعيداً عن الزحام.',
                    'en' => 'Hotel with authentic Bedouin style on a peaceful beach away from crowds.',
                ],
                'rating' => 3,
                'facilities' => [
                    'ar' => 'أكواخ بدوية، مطعم على الشاطئ، رحلات جبلية، كرة طائرة شاطئية',
                    'en' => 'Bedouin huts, Beachfront restaurant, Mountain trips, Beach volleyball',
                ],
                'free_child_age' => 3,
                'adult_age' => 12,
                'first_child_price_percentage' => 30,
                'second_child_price_percentage' => 20,
                'third_child_price_percentage' => 15,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'منتجع العين السخنة',
                    'en' => 'Ain Sokhna Resort',
                ],
                'latitude' => 29.6064,
                'longitude' => 32.3490,
                'phone_key' => '+20',
                'phone' => '0123456775',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'العين السخنة، مصر',
                    'en' => 'Ain Sokhna, Egypt',
                ],
                'description' => [
                    'ar' => 'منتجع عائلي قريب من القاهرة على ساحل البحر الأحمر.',
                    'en' => 'Family resort close to Cairo on the Red Sea coast.',
                ],
                'rating' => 4,
                'facilities' => [
                    'ar' => 'شاطئ رملي، مسابح للأطفال، ملاعب رياضية، مطاعم متنوعة',
                    'en' => 'Sandy beach, Kids pools, Sports fields, Diverse restaurants',
                ],
                'free_child_age' => 4,
                'adult_age' => 12,
                'first_child_price_percentage' => 45,
                'second_child_price_percentage' => 30,
                'third_child_price_percentage' => 20,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق الفيوم البحيرة',
                    'en' => 'Fayoum Lake Hotel',
                ],
                'latitude' => 29.3084,
                'longitude' => 30.8428,
                'phone_key' => '+20',
                'phone' => '0123456774',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'الفيوم، مصر',
                    'en' => 'Fayoum, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق هادئ على بحيرة قارون مع مناظر طبيعية ساحرة.',
                    'en' => 'Peaceful hotel on Lake Qarun with charming natural scenery.',
                ],
                'rating' => 3,
                'facilities' => [
                    'ar' => 'صيد سمك، قوارب، رحلات لوادي الحيتان، مطعم سمك',
                    'en' => 'Fishing, Boats, Wadi El-Hitan trips, Fish restaurant',
                ],
                'free_child_age' => 5,
                'adult_age' => 13,
                'first_child_price_percentage' => 40,
                'second_child_price_percentage' => 25,
                'third_child_price_percentage' => 15,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق الساحل الشمالي',
                    'en' => 'North Coast Hotel',
                ],
                'latitude' => 30.8854,
                'longitude' => 28.6358,
                'phone_key' => '+20',
                'phone' => '0123456773',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'مرسى مطروح، مصر',
                    'en' => 'Marsa Matrouh, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق صيفي على الساحل الشمالي بمياه فيروزية صافية.',
                    'en' => 'Summer hotel on the North Coast with crystal clear turquoise waters.',
                ],
                'rating' => 4,
                'facilities' => [
                    'ar' => 'شاطئ خاص، رياضات شاطئية، بار على الشاطئ، ترفيه ليلي',
                    'en' => 'Private beach, Beach sports, Beach bar, Night entertainment',
                ],
                'free_child_age' => 3,
                'adult_age' => 12,
                'first_child_price_percentage' => 40,
                'second_child_price_percentage' => 25,
                'third_child_price_percentage' => 15,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق رأس سدر الشاطئي',
                    'en' => 'Ras Sudr Beach Hotel',
                ],
                'latitude' => 29.6000,
                'longitude' => 32.7167,
                'phone_key' => '+20',
                'phone' => '0123456772',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'رأس سدر، جنوب سيناء، مصر',
                    'en' => 'Ras Sudr, South Sinai, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق مثالي لممارسة رياضة ركوب الأمواج الشراعية والكايت سيرفينج.',
                    'en' => 'Perfect hotel for windsurfing and kitesurfing enthusiasts.',
                ],
                'rating' => 4,
                'facilities' => [
                    'ar' => 'مركز رياضات مائية، شاطئ رملي، مطعم بحري، تأجير معدات',
                    'en' => 'Water sports center, Sandy beach, Seafood restaurant, Equipment rental',
                ],
                'free_child_age' => 4,
                'adult_age' => 13,
                'first_child_price_percentage' => 45,
                'second_child_price_percentage' => 30,
                'third_child_price_percentage' => 20,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'فندق القاهرة التاريخي',
                    'en' => 'Historic Cairo Hotel',
                ],
                'latitude' => 30.0475,
                'longitude' => 31.2357,
                'phone_key' => '+20',
                'phone' => '0123456771',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'القاهرة الإسلامية، مصر',
                    'en' => 'Islamic Cairo, Egypt',
                ],
                'description' => [
                    'ar' => 'فندق بوتيك في قلب القاهرة التاريخية بالقرب من خان الخليلي.',
                    'en' => 'Boutique hotel in the heart of Historic Cairo near Khan El-Khalili.',
                ],
                'rating' => 4,
                'facilities' => [
                    'ar' => 'تراس على السطح، مقهى تقليدي، جولات سياحية، واي فاي مجاني',
                    'en' => 'Rooftop terrace, Traditional café, Guided tours, Free WiFi',
                ],
                'free_child_age' => 4,
                'adult_age' => 12,
                'first_child_price_percentage' => 50,
                'second_child_price_percentage' => 30,
                'third_child_price_percentage' => 20,
                'additional_child_price_percentage' => 10,
            ],
            [
                'user_id' => $users->random()->id,
                'city_id' => $cities->random()->id,
                'email' => fake()->unique()->safeEmail(),
                'name' => [
                    'ar' => 'منتجع الأقصر الشتوي',
                    'en' => 'Luxor Winter Palace Resort',
                ],
                'latitude' => 25.6989,
                'longitude' => 32.6421,
                'phone_key' => '+20',
                'phone' => '0123456770',
                'status' => Status::Active,
                'address' => [
                    'ar' => 'الأقصر، مصر',
                    'en' => 'Luxor, Egypt',
                ],
                'description' => [
                    'ar' => 'منتجع تاريخي فاخر على ضفاف النيل بحدائق استوائية رائعة.',
                    'en' => 'Historic luxury resort on the Nile banks with magnificent tropical gardens.',
                ],
                'rating' => 5,
                'facilities' => [
                    'ar' => 'حدائق واسعة، حمام سباحة تاريخي، مطاعم راقية، رحلات نيلية',
                    'en' => 'Spacious gardens, Historic pool, Fine dining, Nile cruises',
                ],
                'free_child_age' => 5,
                'adult_age' => 14,
                'first_child_price_percentage' => 55,
                'second_child_price_percentage' => 35,
                'third_child_price_percentage' => 25,
                'additional_child_price_percentage' => 15,
            ],
        ];

        foreach ($hotels as $hotelData) {
            $hotel = Hotel::create($hotelData);

            // Create 3-5 images for each hotel
            File::factory()->count(rand(3, 5))->image()->create([
                'fileable_id' => $hotel->id,
                'fileable_type' => Hotel::class,
            ]);
            // hotelTypes
            $hotelTypes = HotelType::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $hotel->hotelTypes()->attach($hotelTypes);
        }

        // Create additional random hotels with files
        //		Hotel::factory()
        //			->count(15)
        //			->create()
        //			->each(function ($hotel) {
        //				File::factory()
        //					->count(rand(2, 6))
        //					->image()
        //					->create([
        //						'fileable_id' => $hotel->id,
        //						'fileable_type' => Hotel::class,
        //					]);
        //			});
    }
}
