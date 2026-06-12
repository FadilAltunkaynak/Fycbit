<?php
namespace App\Http\Services;

use App\User;
use App\Model\Network;
use App\Model\AdminWalletKey;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use App\Http\Services\Evm\EvmWalletService;

class SystemWalletService
{
    public function __construct()
    {
    }
    public function createSystemWalletProccess($request)
    {
        try {
            $finder = [
                'uid' => $request->uid ?? uniqid() . time()
            ];
            $insertData = [
                "status" => isset($request->status),
            ];

            if (!isset($request->uid)) {
                $insertData['network_id'] = $request->network_id;
                $insertData["address"] = $request->address;

                if (isset($request->private_key))
                    $insertData['pv'] = custom_encrypt($request->private_key);

                if (($request->creation_type == STATUS_INACTIVE)) {
                    $addressResponse = (new EvmWalletService())->createSystemWallet($request->network_id);
                    if (isset($addressResponse['success']) && $addressResponse['success']) {
                        $insertData['address'] = $addressResponse["data"]['address'];
                        $insertData['creation_type'] = STATUS_INACTIVE;
                        $insertData['pv'] = custom_encrypt($addressResponse["data"]['pk']);
                    } else
                        return responseData(false, isset($addressResponse['message']) ? $addressResponse['message'] : __("System wallet generation faild"));
                }
            } else {
                if ($wallet = AdminWalletKey::where('uid', $request->uid ?? "")->first()) {
                    if (isset($request->address))
                        $insertData['address'] = $request->address;
                    //if(isset($request->private_key)) $insertData['pv'] = custom_encrypt($request->private_key);
                } else
                    return responseData(false, __("System wallet not found"));
            }



            if (AdminWalletKey::updateOrCreate($finder, $insertData)) {
                if (isset($request->uid))
                    return responseData(true, __("System wallet updated successfully"));
                return responseData(true, __("System wallet created successfully"));
            }
            if (isset($request->uid))
                return responseData(true, __("System wallet failed to update"));
            return responseData(false, __("System wallet failed to create"));
        } catch (\Exception $e) {
            storeException("createSystemWalletProccess", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function viewSystemWalletey($request)
    {
        try {
            if (!isset($request->id))
                return responseData(false, __('Invalid Request!'));

            if (!isset($request->password))
                return responseData(false, __('Enter Your Password!'));

            if (checkGoogleAuth()) {
                if (!isset($request->google_authenticator)) {
                    return ['success' => false, 'message' => __('Google authenticator code is missing!')];
                }
                $user = auth()->user();
                if (blank($user->google2fa_secret ?? null)) {
                    return ['success' => false, 'message' => __('Google authenticator not setup')];
                }

                $google2fa = new Google2FA();
                $valid = $google2fa->verifyKey($user->google2fa_secret, $request->google_authenticator);
                if (!$valid) {
                    return [
                        'success' => false,
                        'message' => __('Google authentication code is invalid'),
                    ];
                }
            }

            if ($password = auth()->user()->password) {
                if (Hash::check($request->password, $password)) {
                    $id = custom_decrypt($request->id);
                    $adminWallet = AdminWalletKey::find($id);
                    if (isset($adminWallet) && isset($adminWallet->pv)) {
                        return responseData(true, __('Wallet private key details'), [
                            custom_decrypt($adminWallet->pv) ?? __("Wallet private key not found")
                        ]);
                    }
                    return responseData(false, __('Wallet key not found!'));
                }
                return responseData(false, __('Invalid Password!'));
            }
            return responseData(false, __('User not found!'));
        } catch (\Exception $e) {
            return responseData(false, $e->getMessage());
        }

    }

    public function updateSystemWalletey($request)
    {
        if (!isset($request->id))
            return responseData(false, __("Wallet id is required"));
        if (!isset($request->pv))
            return responseData(false, __("Wallet new private key is required"));

        if (checkGoogleAuth()) {
            if (!isset($request->code)) {
                return ['success' => false, 'message' => __('Google authenticator code is missing!')];
            }
            $user = auth()->user();
            if (blank($user->google2fa_secret ?? null)) {
                return ['success' => false, 'message' => __('Google authenticator not setup')];
            }

            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);
            if (!$valid) {
                return [
                    'success' => false,
                    'message' => __('Google authentication code is invalid'),
                ];
            }
        }

        $id = ($request->id);
        // $id = (gettype($id) == 'array') ? 0 : $id;
        if ($wallet = AdminWalletKey::where('uid', $id)->first()) {
            $wallet->pv = custom_encrypt($request->pv);
            if ($wallet->save())
                return responseData(true, __('Wallet private key has been updated successfully'));
            return responseData(false, __('Wallet private key failed to be updated'));
        }
        return responseData(false, __("Wallet not found"));
    }

    public function systemWalletCheckAddress($request)
    {
        if (!isset($request->wallet_key))
            return responseData(false, __('Wallet key is missing'));
        if (!isset($request->network_id))
            return responseData(false, __('Network is missing'));

        if (!$network = Network::find($request->network_id))
            return responseData(false, __('Network not found'));

        return (new EvmWalletService())
            ->checkSystemWalletAddress(
                $network->id,
                $request->wallet_key
            );
    }
}