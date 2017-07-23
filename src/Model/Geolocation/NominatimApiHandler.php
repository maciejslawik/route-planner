<?php
/**
 * File: NominatimApiHandler.php
 *
 * @author      Maciej Sławik <maciekslawik@gmail.com>
 * Github:      https://github.com/maciejslawik
 */

namespace MSlwk\RoutePlanner\Model\Geolocation;

use MSlwk\RoutePlanner\Api\GeolocatorInterface;
use MSlwk\RoutePlanner\Exception\AddressNotFoundException;

/**
 * Class NominatimApiHandler
 *
 * @package MSlwk\RoutePlanner\Model\Geolocation
 */
class NominatimApiHandler implements GeolocatorInterface
{
    const NOMINATIM_URL = 'http://nominatim.openstreetmap.org/search/';

    /**
     * @param string $address
     * @return array
     */
    public function getCoordinates(string $address): array
    {
        $params = $this->getBaseParameters();
        $queryParams = [$address];
        $query = implode(', ', $queryParams);
        $params['q'] = $query;

        $fieldsString = $this->getFieldsString($params);
        $curl = $this->getCurl($fieldsString);

        $curl_response = curl_exec($curl);
        if ($curl_response === false) {
            $this->raiseError();
        }
        curl_close($curl);

        $decoded = json_decode($curl_response, true);

        if (!isset($decoded[0])) {
            $this->raiseError();
        }
        return $decoded[0];
    }

    /**
     * @return null
     * @throws AddressNotFoundException
     */
    private function raiseError()
    {
        throw new AddressNotFoundException();
    }

    /**
     * @return array
     */
    private function getBaseParameters(): array
    {
        return $params = [
            'format' => 'json',
            'addressdetails' => true,
            'limit' => 1
        ];
    }

    /**
     * @param array $parameters
     * @return string
     */
    private function getFieldsString(array $parameters): string
    {
        $fieldsString = '?';
        foreach ($parameters as $key => $value) {
            if (!$value) {
                continue;
            }
            $fieldsString .= $key . '=' . urlencode($value) . '&';
        }
        rtrim($fieldsString, '&');
        return $fieldsString;
    }

    /**
     * @param string $fieldsString
     * @return resource
     */
    private function getCurl(string $fieldsString)
    {
        $curl = curl_init(self::NOMINATIM_URL . $fieldsString);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['application/json; charset=UTF-8',]);
        return $curl;
    }

}
