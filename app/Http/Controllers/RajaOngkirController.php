<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RajaOngkirController extends Controller
{
    private $API_KEY = "f205c02f78c6439129c86537de4f6e38";
    private $baseURL = "https://api.rajaongkir.com/starter/";

    public function getProvinces()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseURL . "province",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "key: {$this->API_KEY}"
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            return response()->json(['error' => $error], 500);
        }

        curl_close($curl);

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            return response()->json(['error' => 'Failed to fetch data from RajaOngkir API.'], $httpCode);
        }

        return response()->json($data);
    }

    public function getCities(Request $request)
    {
        $provinceId = $request->input('province_id');
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseURL . "city?province={$provinceId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "key: {$this->API_KEY}"
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            return response()->json(['error' => $error], 500);
        }

        curl_close($curl);

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            return response()->json(['error' => 'Failed to fetch data from RajaOngkir API.'], $httpCode);
        }

        return response()->json($data);
    }

    public function calculateShipping(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseURL . "cost",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'origin' => $request->input('origin'),
                'destination' => $request->input('destination'),
                'weight' => $request->input('weight'),
                'courier' => $request->input('courier')
            ]),
            CURLOPT_HTTPHEADER => [
                "content-type: application/x-www-form-urlencoded",
                "key: {$this->API_KEY}"
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            return response()->json(['error' => $error], 500);
        }

        curl_close($curl);

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            return response()->json(['error' => 'Failed to calculate shipping cost using RajaOngkir API.'], $httpCode);
        }

        return response()->json($data);
    }
}
