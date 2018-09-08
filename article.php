<?php
//《reis实战》黄健宏译-文章投票网站后端PHP实现 WillLiu 20180908
class article{
    
    const ONE_DAY_IN_SECONDS = 86400;//一天的秒数
    const VOTE_SCORE = 432;//每投一票增加的评分 
    
    const SUCCESS = 1;//成功
    const CONFIG_ERROR = "配置错误";
    const PARAM_ERROR = "参数错误";
    const VOTE_ERROR = "文章投票时间已过期"; 
    
     private $redis;//redis连接对象
 
    /**
     *@desc:初始化redis连接对象 
     */
    public function __construct(){
      $redisConfig = @include('redis.config');
      if(!$redisConfig){
        return self::CONFIG_ERROR;
      }
      $this->redis = new Redis();
      $this->redis->connect($redisConfig['article'][0],$redisConfig['article'][1]); 
    }
    
    /**
     *@desc : 文章投票功能 
     *@param: $user int 用户id
     *@param: $article int 文章id    
     */
    public function article_vote($user, $article){
      //投票的条件：文章处于投票时间之内(约定为距离发布时间的7天之内)
      //更新对应的数据结构:记录文章已投票用户的集合、记录文章评分的有序集合、记录文章信息的散列表
      $user = intval($user);
      $article = intval($article);
      if(!$user || $article){
        return self::PARAM_ERROR;
      }
      $releaseTime = $this->redis->zscore("time:", "article:{$article}");//获取文章发布时间
      if(time() > ($releaseTime + 7 * self::ONE_DAY_IN_SECONDS)){
        return self::VOTE_ERROR;
      }
      if($this->redis->sadd("voted:{$article}", 'user:{$user}')){
        $this->redis->zincrby("score:", "article:{$article}", self::VOTE_SCORE);
        $this->redis->hincrby("article:{$article}", "votes", 1);
      }
       return self::SUCCESS;  
    }
}
?>
