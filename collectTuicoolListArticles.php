<?php
/**
 * Tuicool Article Collect
 * Collect Tuicool's ArticleList All Articles...
 * 配合 phpQuery 一键采集推酷指定文章分类列表中的所有内容~
 *
 * @version 1.0
 * @link https://github.com/Zneiat/collectTuicoolListArticles
 * @author Zneiat <zneiat@163.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

// 引入 phpQuery 类
require_once ('phpQuery/phpQuery.php');

class Tuicool{
    protected $siteBaseUrl = 'http://www.tuicool.com';
    
    /**
     * 采集列表中的所有文章内容
     * @param $reqOneUrl string 列表URL（格式：http://www.tuicool.com/ah/0/{__PAGE__}?lang=1 将页码替换为{__PAGE__}）
     * @param int $startPage 开始页码
     * @param int $endPage 结束页码
     * @return array
     */
    public function collectArticleListArticles($reqOneUrl,$startPage=0,$endPage=1) {
        $dataArr = [];
        for ($Page = $startPage; $Page <= $endPage; $Page++) {
            // $reqOneUrl = "http://www.tuicool.com/ah/0/$Page?lang=1";
            $getOnePage = $this->getURL(str_replace('{__PAGE__}',$Page,$reqOneUrl));
            \phpQuery::newDocument($getOnePage);
            foreach (pq('.single_fake') as $Num => $Value) {
                $ArticleUrl = trim($this->siteBaseUrl . pq($Value)->find('.article-list-title')->attr('href'));
                $dataArr['req_one'][$Num]['title'] = trim(trim(pq($Value)->find('.article-list-title')->text()));
                $dataArr['req_one'][$Num]['url'] = $ArticleUrl;
                $dataArr['req_one'][$Num]['article_cut'] = trim(pq($Value)->find('.article_cut')->text());
                $dataArr['req_one'][$Num]['from'] = trim(pq($Value)->find('.tip > .cut')->text());
                $dataArr['req_one'][$Num]['datetime'] = trim(pq($Value)->find('.tip > span:nth-child(2)')->text());
                $dataArr['req_one'][$Num]['article_thumb'] = str_replace(['!middle', 'http://static0.tuicool.com/images/abs_img_no.jpg'], '', pq($Value)->find('.article_thumb img')->attr('src'));
                
                $getTwoPage = $this->getURL($ArticleUrl);
                \phpQuery::newDocument($getTwoPage);
                $Contant = pq('.contant');
                $dataArr['req_two'][$Num]['title'] = trim($Contant->find('h1')->text());
                $dataArr['req_two'][$Num]['datetime'] = trim(str_replace('时间 ', '', trim($Contant->find('.article_meta .timestamp')->text())));
                $dataArr['req_two'][$Num]['from'] = trim($Contant->find('.article_meta .from > a.from')->text());
                $dataArr['req_two'][$Num]['source_url'] = trim($Contant->find('.article_meta .source > a')->attr('href'));
                $Tags = '';
                foreach ($Contant->find('.article_meta > div:nth-child(3)')->children('a') as $aTag)
                    $Tags .= trim(pq($aTag)->find('.new-label')->text()) . ',';
                $dataArr['req_two'][$Num]['tags'] = trim($Tags, ',');
                $dataArr['req_two'][$Num]['content'] = trim($Contant->find('.article_body > div')->html());
            }
        }
        return $dataArr;
    }
    
    /**
     * 采集热门列表的文章
     * @param int $startPage 开始页码
     * @param int $endPage 结束页码
     * @return array
     */
    public function collectHotListArticle($startPage=0,$endPage=1){
        return $this->collectArticleListArticles('http://www.tuicool.com/ah/0/{__PAGE__}?lang=1',$startPage,$endPage);
    }
    
    /**
     * 采集科技列表的文章
     * @param int $startPage 开始页码
     * @param int $endPage 结束页码
     * @return array
     */
    public function collectSciTechListArticle($startPage=0,$endPage=1){
        return $this->collectArticleListArticles('http://www.tuicool.com/ah/101000000/{__PAGE__}?lang=1',$startPage,$endPage);
    }
    
    /**
     * 采集创投列表的文章
     * @param int $startPage 开始页码
     * @param int $endPage 结束页码
     * @return array
     */
    public function collectVcListArticle($startPage=0,$endPage=1){
        return $this->collectArticleListArticles('http://www.tuicool.com/ah/101040000/{__PAGE__}?lang=1',$startPage,$endPage);
    }
    
    /**
     * 采集数码列表的文章
     * @param int $startPage 开始页码
     * @param int $endPage 结束页码
     * @return array
     */
    public function collectDigitalListArticle($startPage=0,$endPage=1){
        return $this->collectArticleListArticles('http://www.tuicool.com/ah/101050000/{__PAGE__}?lang=1',$startPage,$endPage);
    }
    
    /**
     * 采集技术列表的文章
     * @param int $startPage 开始页码
     * @param int $endPage 结束页码
     * @return array
     */
    public function collectTechListArticle($startPage=0,$endPage=1){
        return $this->collectArticleListArticles('http://www.tuicool.com/ah/20/{__PAGE__}?lang=1',$startPage,$endPage);
    }
    
    /**
     * 采集设计列表的文章
     * @param int $startPage 开始页码
     * @param int $endPage 结束页码
     * @return array
     */
    public function collectDesignListArticle($startPage=0,$endPage=1){
        return $this->collectArticleListArticles('http://www.tuicool.com/ah/108000000/{__PAGE__}?lang=1',$startPage,$endPage);
    }
    
    /**
     * 采集营销列表的文章
     * @param int $startPage 开始页码
     * @param int $endPage 结束页码
     * @return array
     */
    public function collectMarketingListArticle($startPage=0,$endPage=1){
        return $this->collectArticleListArticles('http://www.tuicool.com/ah/114000000/{__PAGE__}?lang=1',$startPage,$endPage);
    }
    
    /**
     * Curl下载一个页面
     * @param $url string URL地址
     * @return mixed
     * @throws Exception
     */
    public function getURL($url){
        $url = preg_replace('/ /', '%20', $url);
        if(function_exists('curl_init')){
            $curl = curl_init($url);
            curl_setopt ($curl, CURLOPT_TIMEOUT, CURL_TIMEOUT);
            curl_setopt ($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36");
            curl_setopt ($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt ($curl, CURLOPT_HEADER, 0);
            curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            @curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, true);
            @curl_setopt ($curl, CURLOPT_MAXREDIRS, 10);
            // ENCODING
            @curl_setopt ($curl, CURLOPT_ENCODING, 'gzip,deflate');
            
            $curlResult = curl_exec($curl);
            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if($httpStatus == 404)
                throw new Exception('404');
            if($httpStatus == 302)
                throw new Exception('302');
            if($curlResult){
                curl_close($curl);
                return $curlResult;
            } else {
                curl_close($curl);
                throw new Exception(curl_error($curl));
            }
        } else {
            throw new Exception("PHP需要开启Curl扩展");
        }
    }
}