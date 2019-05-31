<?php

namespace src;

use Exception;
use libs\Grabber;
use voku\helper\HtmlDomParser;


class Pampik
{
    private $grabber;
    private $products = [];
    private $catagories = [];


    public function __construct()
    {
        $this->grabber = new Grabber();

    }


    /**
     * @param array $catagories
     * @return $this
     * @throws Exception
     */
    public function setCatagories($catagories = [])
    {
        if (!count($catagories)) {
            throw new Exception('Categories can not be empty');
        }

        $this->catagories = $catagories;

        return $this;
    }

    /**
     * @return array
     */
    public function purse()
    {
        foreach ($this->catagories as $url) {
            $resultCategoryHtml = $this->grabber->getHtmlFromUrl($url);
            if ($resultCategoryHtml['code'] == '200') {
                $pages = $this->getProducts($resultCategoryHtml['message']);
                foreach ($pages as $key => $pageUrl) {
                    $result = $this->grabber->getHtmlFromUrl($pageUrl);
                    return $result;
                }
            }
        }
    }


    /**
     * @param $html
     * @return array
     */
    public function getProducts($html)
    {
        $dom = HtmlDomParser::str_get_html($html);

        $urls = [];
        foreach ($dom->find('.product-item__img') as $linkProduct) {
            $urls[] = 'https://pampik.com' . $linkProduct->find('a', 0)->href;
        }
//        echo '<pre>';
//        print_r($urls);
        return $urls;
    }

    public function getOneProduct()
    {

    }

    public function getImg($html)
    {

        $dom = HtmlDomParser::str_get_html($html);

        $img = [];
        foreach ($dom->find('.popup-main-slider__item') as $imgBox) {
            $img[] = $imgBox->getAttribute('src');
        }
        echo '<pre>';
        print_r($img);
        return $img;
    }


}