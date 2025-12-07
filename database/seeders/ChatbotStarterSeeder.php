<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ChatFaq;
use Illuminate\Database\Seeder;

class ChatbotStarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How do I book a hotel?',
                'answer' => 'To book a hotel, go to the Hotels section, browse available hotels, select your preferred hotel and room, choose your check-in and check-out dates, and complete the booking process.',
                'tags' => ['booking', 'hotels', 'getting-started'],
            ],
            [
                'question' => 'How do I book a trip?',
                'answer' => 'To book a trip, navigate to the Trips section, browse available trips, select your preferred trip, choose the number of guests, and proceed with the booking.',
                'tags' => ['booking', 'trips', 'getting-started'],
            ],
            [
                'question' => 'What payment methods are accepted?',
                'answer' => 'We accept various payment methods including credit cards, debit cards, and online payment gateways. Payment details will be provided during the booking process.',
                'tags' => ['payments', 'booking'],
            ],
            [
                'question' => 'How can I view my bookings?',
                'answer' => 'You can view all your bookings by going to the Profile section and selecting "My Bookings". There you will see all your hotel and trip bookings.',
                'tags' => ['booking', 'account', 'profile'],
            ],
            [
                'question' => 'Can I cancel my booking?',
                'answer' => 'Cancellation policies vary depending on the hotel or trip. Please check the specific cancellation policy for your booking or contact our support team for assistance.',
                'tags' => ['booking', 'cancellation', 'support'],
            ],
            [
                'question' => 'How do I update my profile information?',
                'answer' => 'To update your profile, go to the Profile section, click on "Edit Profile", update your information, and save the changes.',
                'tags' => ['account', 'profile'],
            ],
            [
                'question' => 'How do I change my password?',
                'answer' => 'To change your password, go to Profile > Settings > Change Password. Enter your current password and your new password, then save the changes.',
                'tags' => ['account', 'security', 'profile'],
            ],
            [
                'question' => 'What are the check-in and check-out times?',
                'answer' => 'Check-in and check-out times vary by hotel. Typically, check-in is after 2:00 PM and check-out is before 12:00 PM. Specific times will be shown on the hotel details page.',
                'tags' => ['hotels', 'booking'],
            ],
            [
                'question' => 'How do I add items to my favorites?',
                'answer' => 'You can add hotels or trips to your favorites by clicking the heart icon on any listing. Access your favorites from the Favorites section in your profile.',
                'tags' => ['favorites', 'account'],
            ],
            [
                'question' => 'How do I contact customer support?',
                'answer' => 'You can contact our customer support team through the app\'s support section or by using this chatbot. We\'re here to help with any questions or issues.',
                'tags' => ['support', 'help'],
            ],
            [
                'question' => 'كيف أقوم بحجز فندق؟',
                'answer' => 'لحجز فندق، انتقل إلى قسم الفنادق، تصفح الفنادق المتاحة، اختر الفندق والغرفة المفضلة لديك، اختر تواريخ تسجيل الوصول والمغادرة، وأكمل عملية الحجز.',
                'tags' => ['booking', 'hotels', 'arabic'],
            ],
            [
                'question' => 'كيف أقوم بحجز رحلة؟',
                'answer' => 'لحجز رحلة، انتقل إلى قسم الرحلات، تصفح الرحلات المتاحة، اختر رحلتك المفضلة، اختر عدد الضيوف، وتابع عملية الحجز.',
                'tags' => ['booking', 'trips', 'arabic'],
            ],
            [
                'question' => 'ما هي طرق الدفع المقبولة؟',
                'answer' => 'نقبل طرق دفع متنوعة بما في ذلك بطاقات الائتمان والخصم وبوابات الدفع الإلكتروني. سيتم توفير تفاصيل الدفع أثناء عملية الحجز.',
                'tags' => ['payments', 'booking', 'arabic'],
            ],
        ];

        foreach ($faqs as $faq) {
            ChatFaq::create($faq);
        }

        $this->command->info('Chatbot starter FAQs seeded successfully!');
    }
}

