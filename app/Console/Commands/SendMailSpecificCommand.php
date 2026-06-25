<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMailSpecificCommand extends Command
{
    protected $signature = 'mail:send-specific';
    protected $description = 'Resend mail to specific users missing whatsapp number with HTML design';

    public function handle()
    {
        $usersData = [
            // ['name' => 'NURA MUHAMMAD SULAIMAN', 'email' => 'flawlessdreevo@gmail.com'],
            // ['name' => 'Abdullahi Tester', 'email' => 'yerwaexpressnews247@gmail.com'],
            // ['name' => 'Muhammad Muhammad Barnawi', 'email' => 'barnawimuhd@gmail.com'],
            // ['name' => 'Sanusi Umar Barambu', 'email' => 'telecomarewa@gmail.com'],
            // ['name' => 'Quin Marks', 'email' => 'pomisujuk@mailinator.com'],
            // ['name' => 'Yunusa Bunu Zanna', 'email' => 'mrmenk51@gmail.com'],
            // ['name' => 'Isma’il', 'email' => 'harunaismail171@gmail.com'],
            // ['name' => 'Ismail Malah (iMalah)', 'email' => 'ismailmalah007@gmail.com'],
            // ['name' => 'ALIYU SHITTU MUHAMMED', 'email' => 'aleeums007@hotmail.com'],
            // ['name' => 'NADALLI AMINU', 'email' => 'aminunadalli@gmail.com'],
            ['name' => 'Ibrahim Muhammad Hashim', 'email' => 'ibramuhd7@gmail.com'],
            ['name' => 'Saidu Mustapha', 'email' => 'xperrtmedia@gmail.com'],
            ['name' => 'Victor Karfe', 'email' => 'karfeviktor@gmail.com'],
            ['name' => 'Nasir', 'email' => 'ahmednasiru2@gmail.com'],
            ['name' => 'Aminu Umar', 'email' => 'aminuchalaboy91@gmail.com'],
            ['name' => 'Bilyaminu Sabiu', 'email' => 'bilyaminusabiu1@gmail.com'],
            ['name' => 'Umar Abdulkadir', 'email' => 'umarabdulkadir300@gmail.com'],
            ['name' => 'Ayuba Abubakar Baba', 'email' => 'ayubaabubakarbaba553@gmail.com'],
            ['name' => 'Nuhu Salihu Yusuf', 'email' => 'nuhudomino@gmail.com'],
            ['name' => 'Auwal  Lawal', 'email' => 'lawalauwal2044@gmail.com'],
            ['name' => 'Ibrahim Mohammed', 'email' => 'albishirdecorations@gmail.com'],
            ['name' => 'Raphael', 'email' => 'raphealdanjumaa1994@gmail.com'],
            ['name' => 'Magaji Juji', 'email' => 'mashazadouble77@gmail.com'],
            ['name' => 'Abubakar Abdullahi', 'email' => 'abdultycoon24@gmail.com'],
            ['name' => 'Muhammed Bashir Muhammed', 'email' => 'muhammadb4life@gmail.com'],
            ['name' => 'Salisu Yahya magashi', 'email' => 'salisuyahayamagashi@gmail.com'],
            ['name' => 'Ahmad Dan gaske', 'email' => 'adangaske89@gmail.com'],
            ['name' => 'Sunusi Nalele', 'email' => 'sunusinalele2@gmail.com'],
            ['name' => 'Tukur Ahmed Tijjani', 'email' => 'ahmedtijjanitukur@gmail.com'],
            ['name' => 'Musa Hassan', 'email' => 'abulhisham387@gmail.com'],
            ['name' => 'Ahmad A. Maizube', 'email' => 'amaizube@gmail.com'],
            ['name' => 'Said Kabir said', 'email' => 'saidkabirsaid786@gmail.com'],
            ['name' => 'Abubakar Sahabi', 'email' => 'abbercarjabo@gmail.com'],
            ['name' => 'Habibatu Alih', 'email' => 'zuhabby08@gmail.com'],
            ['name' => 'Yusuf Shehu', 'email' => 'yusufsheyu501@gmail.com'],
            ['name' => 'Abdurrahman Umar Abubakar', 'email' => 'abdurrahmanumarabubakar@gmail.com'],
            ['name' => 'Dalhatu usama Usman', 'email' => 'dalhatuusamausman@gmail.com'],
            ['name' => 'Sumayya Abdullahi', 'email' => 'summayyaabdullahi30@gmail.com'],
            ['name' => 'Abdulmalik Abubakar', 'email' => 'abdulmlikusman985@gmail.com'],
            ['name' => 'Qawam Musiliu', 'email' => 'qmusiliu@gmail.com'],
            ['name' => 'HUSSAINI YAKUBU USMAN', 'email' => 'hussainiusmanyakubu623@gmail.com'],
            ['name' => 'Badamasi idris badamasi', 'email' => 'badamasiidrisbadamasi656@gmail.com'],
        ];

        $appName = env('APP_NAME', 'Elbildad Services Ltd');

        $count = 0;
        foreach ($usersData as $user) {
            
            $htmlContent = <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f4f7; color: #333333; margin: 0; padding: 0; }
                    .wrapper { width: 100%; table-layout: fixed; background-color: #f4f4f7; padding: 40px 0; }
                    .main-content { background-color: #ffffff; max-width: 600px; margin: 0 auto; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
                    .header { background-color: #0b1c3c; color: #ffffff; padding: 25px; text-align: center; }
                    .header h2 { margin: 0; font-size: 24px; font-weight: 600; letter-spacing: 0.5px; }
                    .body { padding: 35px; line-height: 1.6; font-size: 15px; }
                    .footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 13px; color: #64748b; border-top: 1px solid #e2e8f0; }
                    .security-box { background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 18px; margin-top: 30px; font-size: 14px; color: #166534; border-radius: 4px; }
                    .security-box strong { color: #14532d; }
                    .highlight-box { background-color: #eff6ff; border: 1px solid #bfdbfe; padding: 15px; border-radius: 6px; margin: 20px 0; }
                    ul { padding-left: 20px; margin: 10px 0; }
                    li { margin-bottom: 8px; }
                </style>
            </head>
            <body>
                <div class="wrapper">
                    <div class="main-content">
                        <div class="header">
                            <h2>{$appName}</h2>
                        </div>
                        <div class="body">
                            <p>Dear <strong>{$user['name']}</strong>,</p>
                            <p>We are reaching out regarding the Request for Quotation (RFQ) you recently submitted on our platform.</p>
                            <p>We noticed that your contact information is incomplete, as your phone number was not provided. To ensure a smooth process and allow our sourcing team to continue assisting you, please forward us your active WhatsApp number.</p>
                            
                            <div class="highlight-box">
                                <strong>How to update your number:</strong>
                                <ul>
                                    <li>Reply directly to this email with your WhatsApp number.</li>
                                    <li>Or, send a message to us directly via WhatsApp at: <strong>+234 803 277 5756</strong></li>
                                </ul>
                            </div>

                            <p>Thank you for choosing {$appName}. We look forward to fulfilling your request swiftly.</p>
                            
                            <div class="security-box">
                                <strong>🔒 Security Tip:</strong><br><br>
                                Always ensure communications are coming from our official website: <a href="https://elbildadservices.ng" style="color: #15803d; text-decoration: underline;">https://elbildadservices.ng</a>.<br><br>
                                If you are ever unsure, please do not hesitate to confirm by calling our official phone number: <strong>+234 803 277 5756</strong>.
                            </div>
                        </div>
                        <div class="footer">
                            &copy; {$appName}. All rights reserved.<br>
                            <a href="https://elbildadservices.ng" style="color: #64748b; text-decoration: none; margin-top: 5px; display: inline-block;">Visit our website</a>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            HTML;

            try {
                Mail::html($htmlContent, function ($message) use ($user) {
                    $message->to($user['email'])
                            ->subject('Action Required: Please Provide Your WhatsApp Number for Your RFQ');
                });
                $count++;
                $this->info("Sent HTML email to {$user['email']}");
                
                // Add a small delay to avoid rate limits
                usleep(500000); // 0.5s delay
            } catch (\Exception $e) {
                $this->error("Failed to send to {$user['email']}: " . $e->getMessage());
            }
        }

        $this->info("Finished. Total HTML emails sent: $count");
    }
}
