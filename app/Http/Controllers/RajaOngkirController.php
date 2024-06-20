<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class RajaOngkirController extends Controller
{
    private $API_KEY = "f205c02f78c6439129c86537de4f6e38";
    private $baseURL = "https://api.rajaongkir.com/starter/";

    public function getProvinces()
    {
        $client = new Client();

        try {
            $response = $client->get($this->baseURL . 'province', [
                'headers' => [
                    'key' => $this->API_KEY,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json($data);
        } catch (RequestException $e) {
            return response()->json(['error' => 'Failed to fetch data from RajaOngkir API.', 'message' => $e->getMessage()], 500);
        }
    }

    public function getCities(Request $request)
    {
        $provinceId = $request->input('province_id');
        $client = new Client();

        try {
            $response = $client->get($this->baseURL . "city?province={$provinceId}", [
                'headers' => [
                    'key' => $this->API_KEY,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json($data);
        } catch (RequestException $e) {
            return response()->json(['error' => 'Failed to fetch data from RajaOngkir API.', 'message' => $e->getMessage()], 500);
        }
    }

    public function calculateShipping(Request $request)
    {
        $client = new Client();

        try {
            $response = $client->post($this->baseURL . 'cost', [
                'headers' => [
                    'content-type' => 'application/x-www-form-urlencoded',
                    'key' => $this->API_KEY,
                ],
                'form_params' => [
                    'origin' => $request->input('origin'),
                    'destination' => $request->input('destination'),
                    'weight' => $request->input('weight'),
                    'courier' => $request->input('courier'),
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json($data);
        } catch (RequestException $e) {
            return response()->json(['error' => 'Failed to calculate shipping cost using RajaOngkir API.', 'message' => $e->getMessage()], 500);
        }
    }
}
