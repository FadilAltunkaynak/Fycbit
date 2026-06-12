<?php
namespace Modules\P2P\Http\Repository;

use App\Model\CountryList;
use Modules\P2P\Entities\PPaymentMethod;

class PaymentMethodRepository extends BaseRepository
{

    public function __construct() {
    }

    public function getCountry()
    {
        try {
            $data = [
                'where' => ['status', STATUS_ACTIVE],
                'get' => []
            ];
            return $this->getModelData(CountryList::class, $data);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }
    
    
    public function paymentMethodSave($data)
    {
        try {
            return $this->createOrUpdate(PPaymentMethod::class, $data);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }
}