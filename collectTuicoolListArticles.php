<?php
/**
 * Tuicool Article Collect
 * Collect Tuicool's ArticleList All Articles...
 * 配合 phpQuery 一键采集推酷指定文章分类列表中的所有内容~
 *
 * @version 1.1
 * @link https://github.com/Zneiat/collectTuicoolListArticles
 * @author Zneiat <zneiat@163.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

// 引入 phpQuery 类
require_once ('phpQuery/phpQuery.php');

class Tuicool
{
    protected $siteBaseUrl = 'http://www.tuicool.com';
    public $loginCookie = null;
    
    public function run() {
        $this->loginCookie = $this->tuicoolLoginGetCookieAction('example@test.com','1234567890');
        var_dump($this->collectHotListArticle(0,1));
    }
    
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
            $getOnePage = $this->getURL(str_replace('{__PAGE__}',$Page,$reqOneUrl),$this->loginCookie);
            \phpQuery::newDocument($getOnePage);
            foreach (pq('.single_fake') as $Num => $Value) {
                $ArticleUrl = trim($this->siteBaseUrl . pq($Value)->find('.article-list-title')->attr('href'));
                $dataArr[$Num]['req_one']['title'] = trim(trim(pq($Value)->find('.article-list-title')->text()));
                $dataArr[$Num]['req_one']['url'] = $ArticleUrl;
                $dataArr[$Num]['req_one']['article_cut'] = trim(pq($Value)->find('.article_cut')->text());
                $dataArr[$Num]['req_one']['from'] = trim(pq($Value)->find('.tip > .cut')->text());
                $dataArr[$Num]['req_one']['datetime'] = trim(pq($Value)->find('.tip > span:nth-child(2)')->text());
                $dataArr[$Num]['req_one']['article_thumb'] = str_replace(['!middle', 'http://static0.tuicool.com/images/abs_img_no.jpg'], '', pq($Value)->find('.article_thumb img')->attr('src'));
                
                $getTwoPage = $this->getURL($ArticleUrl,$this->loginCookie);
                \phpQuery::newDocument($getTwoPage);
                $Contant = pq('.contant');
                $dataArr[$Num]['req_two']['title'] = trim($Contant->find('h1')->text());
                $dataArr[$Num]['req_two']['datetime'] = trim(str_replace('时间 ', '', trim($Contant->find('.article_meta .timestamp')->text())));
                $dataArr[$Num]['req_two']['from'] = trim($Contant->find('.article_meta .from > a.from')->text());
                $dataArr[$Num]['req_two']['source_url'] = trim($Contant->find('.article_meta .source > a')->attr('href'));
                $Tags = '';
                foreach ($Contant->find('.article_meta > div:nth-child(3)')->children('a') as $aTag)
                    $Tags .= trim(pq($aTag)->find('.new-label')->text()) . ',';
                $dataArr[$Num]['req_two']['tags'] = trim($Tags, ',');
                $dataArr[$Num]['req_two']['content'] = trim($Contant->find('.article_body > div')->html());
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
     * 推酷登录，获取Cookie
     * @param $username
     * @param $passwd
     * @return mixed
     */
    public function tuicoolLoginGetCookieAction($username,$passwd){
        $reqLoginPageGetToken = $this->getURL('http://www.tuicool.com/login');
        \phpQuery::newDocument($reqLoginPageGetToken);
        $Token = pq('head > meta[name=csrf-token]')->attr('content');
        $reqLoginGetCookie = $this->getURL('http://www.tuicool.com/login',null,['utf8'=>'✓','authenticity_token'=>$Token,'email'=>$username,'password'=>$passwd,'remember'=>'1'],null,true);
        return $reqLoginGetCookie;
    }
    
    /**
     * Curl下载一个页面
     * @param $url
     * @param null $cookie
     * @param null $post
     * @param null $header
     * @param bool $returnCookie
     * @return mixed
     * @throws Exception
     */
    public function getURL($url,$cookie=null,$post=null,$header=null,$returnCookie=false){
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
            @curl_setopt ($curl, CURLOPT_ENCODING, 'gzip,deflate'); // 解码
            if(!is_null($cookie)) @curl_setopt ($curl, CURLOPT_COOKIE,$cookie); // 携带 cookie 字符串
            if(!is_null($post)) @curl_setopt($curl, CURLOPT_POSTFIELDS, $post); // Array
            
            $Header = array();
            $Header[0]  = "Accept: text/xml,application/xml,application/xhtml+xml,";
            $Header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
            $Header[] = "Cache-Control: max-age=0";
            $Header[] = "Connection: keep-alive";
            $Header[] = "Keep-Alive: 300";
            $Header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
            $Header[] = "Accept-Language: zh-CN,zh;q=0.8";
            $Header[] = "Pragma: "; // browsers keep this blank.
            if(!is_null($header)){
                foreach ($header as $item){
                    $Header[] = $item;
                }
            }
            curl_setopt($curl,CURLOPT_HTTPHEADER,$Header); // 携带请求 header 数据
            
            if($returnCookie) @curl_setopt($curl,CURLOPT_HEADER, TRUE);
            
            $curlResult = curl_exec($curl);
            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if($httpStatus == 404)
                throw new Exception('404');
            if($httpStatus == 302)
                throw new Exception('302');
            if($curlResult){
                curl_close($curl);
                if($returnCookie){
                    preg_match_all('/Set-Cookie:(.*);/i', $curlResult, $results);
                    return $results[1][0];
                }
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