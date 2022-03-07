<?php

namespace ApKourma\WeatherApi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Tuitest extends Command
{
    /**
     * The name of the command (the part after "bin/weather").
     *
     * @var string
     */
    protected static $defaultName = 'tui test';
    
    /***
     * Const Variables
     */
    const WeatherAPIEndpoint = 'http://api.weatherapi.com/v1/current.json?key=';
    const WeatherAPIKey      = '4d84743f1c2d4543bb4163251222802';

    /**
     * The command description shown when running "php bin/weather list".
     *
     * @var string
     */
    protected static $defaultDescription = 'Processed city';

    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return string
     */
    protected function execute(InputInterface $input, OutputInterface $output): string
    {
        $io = new SymfonyStyle($input, $output);
        
        /**
        * $TuiCities response is an array ['London', 'Amsterdam', 'Berlin', 'Paris', 'Rome', 'Milan', 'Barcelona'] 
        */
        $TuiCities = self::serviceApi( '/api/v3/cities' );

        $textResponse  = '';
        $errorResponse = '';

        foreach($TuiCities as $TuiCity) {
            $responseWeater = self::serviceApi( self::WeatherAPIEndpoint . self::WeatherAPIEndpoint 
            . 'q=' . $TuiCity . '&days=2&aqi=no&alerts=no');

            if (isset($responseWeater['error'])) {
                $errorResponse .= 'Processed city ' . $TuiCity . '| ' . $responseWeater['error'] . '\n';
                continue;
            }

            $textResponse .= 'Processed city' . $TuiCity . ' |' 
            . $responseWeater['forecast']['forecastday'][0]['day']['condition']['text']
            . ' - ' 
            . $responseWeater['forecast']['forecastday'][1]['day']['condition']['text']
            . '\n'; 
        }
        
        if (empty( trim($textResponse) )) {
            $io->error($errorResponse));
        } else {
            $io->success($textResponse);
        }

        return Command::SUCCESS;
    }

    /**
     * Execute Curl command
     *
     * @param  string  $url
     * @return array
     */
    private static function serviceApi(string $url): array
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        if(curl_errno($ch)){
            $resp = ['error' => 'Request Error:' . curl_error($ch)];
        }

        curl_close($curl);

        return json_decode($resp);
    }
}