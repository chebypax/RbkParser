<?php

namespace App\Service;

use App\Service\Interfaces\PostParserInterface;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractPostParser implements PostParserInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {

        $this->em = $em;
    }

    public function getHtml(string $url): string
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL,$url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Test Rbk Parser');
        $html = curl_exec($curl_handle);
        curl_close($curl_handle);

        return $html;
    }

    abstract public function getFeedPostsData(string $html): array;
}
