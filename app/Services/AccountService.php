<?php

namespace App\Services;

use App\Services\ApiRequests\ApiUtils\EndPointManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Store;

class AccountService extends BasePipeline
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function accountVerifyPage()
    {
        return view('accountVerify');
    }

    public function accountVerify()
    {
        $token = $this->request->get('token');

        if ($token == 'aerochat@2025') {
            return redirect()->route('accountDashboard')->with('success', 'Account verified successfully');
        }

        return redirect()->back()->with('error', 'Invalid token');
    }

    public function accountDashboard()
    {
        return view('homePage');
    }

    public function scriptIntegrationApi()
    {
        $store_enable = $this->request->get('store_enable');

        if ($store_enable == 'on') {
            $this->insertScriptTag();
            return redirect()->back()->with('success', 'Aerochat script tag inserted successfully');
        } else {
            $this->deleteScriptTag();
            return redirect()->back()->with('success', 'Aerochat script tag deleted successfully');
        }

        return redirect()->back()->with('error', 'Invalid token');
    }

    public function insertScriptTag()
    {
        $endpoint = (new EndPointManager())->ShopifyURLForStore(['myshopify_domain' => $this->request->shop], 'graphql.json');
        $data = [
            'headers' => [
                'X-Shopify-Access-Token' => $this->request->access_token,
            ],
            'json' => [
                "query" => "mutation ScriptTagCreate(\$input: ScriptTagInput!) { scriptTagCreate(input: \$input) { scriptTag { id cache createdAt displayScope src updatedAt } userErrors { field message } } }",
                "variables" => [
                    "input" => [
                        'src' => EndPointManager::SHOPIFY_SCRIPT_URL . EndPointManager::VERIFICATIONID . '&color=#2e90fa',
                        'displayScope' => 'ONLINE_STORE',
                        'cache' => true
                    ]
                ]
            ]
        ];

        [$status, $content, $statusCode] = $this->curlInit($endpoint,  $data);
        if (!$status || $statusCode != 200) {
            return;
        }
        $content = json_decode($content);
        $scriptTagId = $content->data->scriptTagCreate->scriptTag->id;
        $store = Store::where('myshopify_domain', 'aerochat-laravel-app.myshopify.com')->first();
        $store->script_tag_id = $scriptTagId;
        $store->save();
        return response()->json(['data' => $content, 'store' => $store]);
    }

    public function deleteScriptTag()
    {
        $endpoint = (new EndPointManager())->ShopifyURLForStore(['myshopify_domain' => $this->request->shop], 'graphql.json');
        $store = Store::where('myshopify_domain', $this->request->shop)->first();
        $storeScriptId = $store->script_tag_id;
        $data = [
            'headers' => [
                'X-Shopify-Access-Token' => $this->request->access_token,
            ],
            'json' => [
                "query" => "mutation ScriptTagDelete(\$id: ID!) { scriptTagDelete(id: \$id) { deletedScriptTagId userErrors { field message } } }",
                "variables" => [
                    "id" => $storeScriptId
                ]
            ]
        ];
        [$status, $content, $statusCode] = $this->curlInit($endpoint,  $data);
        if (!$status || $statusCode != 200) {
            return;
        }
        $res = json_decode($content);
        $store->script_tag_id = null;
        $store->save();
        return response()->json(['store' => $store]);
    }
}
