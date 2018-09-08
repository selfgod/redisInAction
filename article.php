<?php
//��reisʵս���ƽ�����-����ͶƱ��վ���PHPʵ�� WillLiu 20180908
class article{
    
    const ONE_DAY_IN_SECONDS = 86400;//һ�������
    const VOTE_SCORE = 432;//ÿͶһƱ���ӵ����� 
    
    const SUCCESS = 1;//�ɹ�
    const CONFIG_ERROR = "���ô���";
    const PARAM_ERROR = "��������";
    const VOTE_ERROR = "����ͶƱʱ���ѹ���"; 
    
     private $redis;//redis���Ӷ���
 
    /**
     *@desc:��ʼ��redis���Ӷ��� 
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
     *@desc : ����ͶƱ���� 
     *@param: $user int �û�id
     *@param: $article int ����id    
     */
    public function article_vote($user, $article){
      //ͶƱ�����������´���ͶƱʱ��֮��(Լ��Ϊ���뷢��ʱ���7��֮��)
      //���¶�Ӧ�����ݽṹ:��¼������ͶƱ�û��ļ��ϡ���¼�������ֵ����򼯺ϡ���¼������Ϣ��ɢ�б�
      $user = intval($user);
      $article = intval($article);
      if(!$user || $article){
        return self::PARAM_ERROR;
      }
      $releaseTime = $this->redis->zscore("time:", "article:{$article}");//��ȡ���·���ʱ��
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
