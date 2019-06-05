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
    public $filename = 'test.txt';

    public function __construct()
    {
        $this->grabber = new Grabber();

    }

    public function getCatagories()
    {
        return $this->catagories;
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
    public function parse()
    {
        foreach ($this->catagories as $url) {
            $resultCategoryHtml = $this->grabber->getHtmlFromUrl($url);

            if ($resultCategoryHtml['code'] == '200') {
                $pages = $this->Pagination($resultCategoryHtml['message']);

                if (count($pages)) {
                    foreach ($pages as $key => $pageUrl) {
                        $resultCategoryHtml = $this->grabber->getHtmlFromUrl($pageUrl);

                        if ($resultCategoryHtml['code'] == 200) {
                            $productUrls = $this->getProducts($resultCategoryHtml['message']);

                            if (count($productUrls)) {
                                foreach ($productUrls as $key => $productUrl) {
                                    $resultCategoryHtml = $this->grabber->getHtmlFromUrl($productUrl);

                                    if ($resultCategoryHtml['code'] == '200') {
                                        $this->products[] = $this->getOneProduct($resultCategoryHtml['message']);
                                        $this->write();
                                        $this->products = [];
                                        exit;
                                    }
                                }
                            }
                        }

                    }
                }
            }
        }

        return $this->products;
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

        return $urls;
    }

    /**
     * @param $html
     * @return array
     */
    public function getOneProduct($html)
    {
        $dom = HtmlDomParser::str_get_html($html);

        $addImages = $this->getImg($html);
        $specifications[] = $this->getSpecifications($html);


        $product = [
            'img' => $addImages,
            'title' => $dom->find('.page-title', 0)->plaintext,
            'price' => $dom->find('.product-info__price-current', 0)->plaintext,
            'articul' => $dom->find('.product__art', 0)->plaintext,
            'description' => $specifications,
        ];

        return $product;

    }

    /**
     * @param $html
     * @return array
     */
    public function getImg($html)
    {

        $dom = HtmlDomParser::str_get_html($html);

        $img = [];
        foreach ($dom->find('.popup-main-slider__item .popup-main-slider__img') as $imgBox) {
            $img[] = 'https://pampik.com' . $imgBox->getAttribute('data-src');
        }
        return $img;
    }

    /**
     * @param $html
     * @return array
     */
    public function getSpecifications($html)
    {
        $dom = HtmlDomParser::str_get_html($html);
        $description = [];

        foreach ($dom->find('.specifications__list .specifications__list-item') as $product) {

            $item = [];
            $item['name'] = $product->find('.specifications-desc', 0)->plaintext;
            $item['value'] = $product->find('.specifications-val', 0)->plaintext;

            $description[] = $item;
        }
        return $description;

    }

    /**
     * @param $html
     * @return array
     */
    public function Pagination($html)
    {
        $dom = HtmlDomParser::str_get_html($html);
        $categoryUrl = $dom->find('link[rel=canonical]', 0)->href;
        $paginationUrl = [];
        foreach ($dom->find('.pagination__page ') as $paginationPages) {
            $pageCount = $paginationPages->find('a', 0)->getAttribute('data-page');
        }
        if ($pageCount) {
            for ($i = 1; $i <= $pageCount; $i++) {
                $paginationUrl[] = $categoryUrl . '/page/' . $i . '#filter-result';
            }
        }


        return $paginationUrl;
    }

    public function write()
    {
        $str_value = serialize($this->products);

        if (is_writable($this->filename)) {

            if (!$handle = fopen($this->filename, 'a')) {
                echo "Не могу открыть файл ($this->filename)";
                exit;
            }

            if (fwrite($handle, $str_value) === FALSE) {
                echo "Не могу произвести запись в файл ($this->filename)";
                exit;
            }

            echo "Записали  в файл ($this->filename)";

            fclose($handle);

        } else {
            echo "Файл $this->filename недоступен для записи";
        }
    }

}