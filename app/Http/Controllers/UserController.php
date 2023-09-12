<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
    public function google()
    {
        return Socialite::driver('google')->redirect(config());
    }

    public function handleGoogleProviderCallback()
    {
        $callback = Socialite::driver('google')->stateless()->user();

        $data = [
            'name' => $callback->getName(),
            'email' => $callback->getEmail(),
            'avatar' => $callback->getAvatar(),
            'email_verified_at' => date('Y-m-d H:i:s', time()),
        ];

        $user = User::firstOrCreate(['email' => $data['email']], $data);
        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    public function github()
    {
        return Socialite::driver('github')->redirect();
    }


    public function handleGithubProviderCallback()
    {
        return Socialite::driver('github')
            ->setScopes(['read:user', 'public_repo'])
            ->redirect();

        //$callback = Socialite::driver('github')->user();

        //return $callback;
    }

    public function tiktok()
    {
        $appId = config('services.tiktok.client_id');
        $redirectUri = urlencode(config('services.tiktok.redirect'));
        return redirect()->to("https://www.tiktok.com/v2/auth/authorize?client_key={$appId}&scope=user.info.basic,video.list&response_type=code&redirect_uri={$redirectUri}&state=csrfToken");
    }

    public function handleTiktokProviderCallback(Request $request)
    {
        $code = $request->code;
        if (empty($code)) return redirect()->route('home')->with('error', 'Failed to login with Tiktok.');

        $appId = config('services.tiktok.client_id');
        $secret = config('services.tiktok.client_secret');
        $redirectUri = config('services.tiktok.redirect');

        $client = new Client();

        // Get access token
        $response = $client->request('POST', 'https://open.tiktokapis.com/v2/oauth/token', [
            'form_params' => [
                'client_key' => $appId,
                'client_secret' => $secret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return redirect()->route('home')->with('error', 'Unauthorized login to Instagram.');
        }

        $content = $response->getBody()->getContents();
        $oAuth = json_decode($content);

        dd($oAuth);
    }

    public function facebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookProviderCallback()
    {
        $callback = Socialite::driver('facebook')->stateless()->user();

        //$endpoint = "https://graph.facebook.com/v17.0/me?fields=id%2Cname%2Clikes%2Cprofile_pic%2Cconversations%2Cfeed%2Cposts&access_token=".$callback->token;

        $user_post = "https://graph.facebook.com/v17.0/".$callback->id."/posts?access_token=".$callback->token;

        $response = Http::get($user_post);

        $content = json_decode($response->getBody(), true);

        dd($content);
    }

    public function instagram()
    {
        $appId = config('services.instagram.client_id');
        $redirectUri = urlencode(config('services.instagram.redirect'));
        return redirect()->to("https://api.instagram.com/oauth/authorize?client_id={$appId}&redirect_uri={$redirectUri}&scope=user_profile,user_media&response_type=code");
    }

    public function handleInstagramProviderCallback(Request $request)
    {
        $code = $request->code;
        if (empty($code)) return redirect()->route('home')->with('error', 'Failed to login with Instagram.');

        $appId = config('services.instagram.client_id');
        $secret = config('services.instagram.client_secret');
        $redirectUri = config('services.instagram.redirect');

        $client = new Client();

        // Get access token
        $response = $client->request('POST', 'https://api.instagram.com/oauth/access_token', [
            'form_params' => [
                'client_id' => $appId,
                'client_secret' => $secret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return redirect()->route('home')->with('error', 'Unauthorized login to Instagram.');
        }

        $content = $response->getBody()->getContents();
        $content = json_decode($content);

        $accessToken = $content->access_token;
        $userId = $content->user_id;

        // Get user info
        $response = $client->request('GET', "https://api.instagram.com/v1/users/self/follows?access_token={$accessToken}");
        //$response = $client->request('GET', "https://graph.instagram.com/me?fields=id,username,account_type,media_count&access_token={$accessToken}");
        //$response = $client->request('GET', "https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,thumbnail_url&access_token={$accessToken}");
        $content = $response->getBody()->getContents();
        $oAuth = json_decode($content);

        // do your code here
        dd($oAuth);
    }

    public function cobaAccessToken()
    {
        //curl -i -X GET \
        $endpoint = "https://graph.facebook.com/v17.0/me?fields=id%2Cname%2C&access_token=EAACDi7K26Y0BO8FYo9rynXgq8kF7wrdiqEaevxFv4cA6gBuZBChrMXZCdJSDsQYcD3GKCvJ6eVyUs9DkPbuGgaa3MBKjD9NUPDjXwugFZArFktwvMXjCpkJzoV7oHjGcRrZCj00YnGc0tmdqjQb2pkHUj4OdANScafgjhKv4ROVaoqQ4GZCZBLd4lyQzheezLX2xScenJwbaLI4HQ5JAZBv1VfMIAZDZD";

        $response = Http::get($endpoint);

        $content = json_decode($response->getBody(), true);

        dd($content);
    }

    public function OpenAiApiTest(Request $request)
    {
        // Ambil teks yang ingin Anda proses dari request
        $inputText = $request->input('text');

        // Ambil kunci API dari .env
        $apiKey = config('services.openai.api_key');

        // Buat instance client Guzzle untuk melakukan permintaan ke API OpenAI
        $client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);


        // Lakukan permintaan ke API OpenAI
        $response = $client->post('engines/davinci/completions', [
            'json' => [
                'prompt' => $inputText,
                'max_tokens' => 50, // Jumlah token maksimum yang ingin Anda hasilkan
            ],
        ]);

        // Ambil hasil respons dari API
        $data = json_decode($response->getBody(), true);

        // Ambil teks yang dihasilkan dari respons
        $generatedText = $data['choices'][0]['text'];
        dd($data);
        dd($generatedText);

        // Kembalikan teks yang dihasilkan sebagai respons JSON
        //return response()->json(['generated_text' => $generatedText]);
    }
}
