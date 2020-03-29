<?php namespace Diveramkt\Getinstagram\Components;

use System\Classes\ApplicationException;
use Cms\Classes\ComponentBase;
use Diveramkt\Getinstagram\Models\Settings;
use Cache;
use Session;

class Instagramfeed extends ComponentBase
{

    public $page, $count, $imgs, $links, $itens;
    public function componentDetails()
    {
        return [
            'name'        => 'Instagram feed',
            'description' => 'Pegar imagens e links das últimas postagens'
        ];
    }

    public function defineProperties()
    {
        return [
            'instagram' => [
                'description' => 'Nome do Instagram (www.instagram.com/{name}',
                'title' => 'Name',
                'default' => '',
                'type' => 'string',
                'validationPattern' => '^[a-zA-Z0-9]+$',
                'validationMessage' => 'The hashtag may only contain alphanumerical characters',
            ],
        ];
    }

    public function onRun()
    {
        $this->controller->addCss('/plugins/lyra/hashtag/assets/css/main.css');

        $this->page=$this->getinstagram($this->property('instagram'));
        $this->count=count($this->page['imgs']);
        $this->imgs=$this->page['imgs'];
        $this->links=$this->page['links'];
        $this->itens=$this->page['itens'];
    }


    // ULTIMOS POSTS INSTAGRAM
    public function getinstagram($instagram=''){

        $settings = Settings::instance();
        if (!strlen($settings->cache)) {
            throw new ApplicationException('Cache is not configured. Please configure it on the System / Settings / Hashtag page.');
        }

        if (Cache::has('lyra_hashtag') && Session::get('instagram_select') == $this->property('instagram')) {
            return Cache::get('lyra_hashtag');
        }
        Session::put('instagram_select', $this->property('instagram'));

        $url='http://instagram.com/'.$instagram;
        $html=file_get_contents($url);

        preg_match('/\"followed_by\"\:\s?\{\"count\"\:\s?([0-9]+)/',$html,$m);
        if(isset($m[1])) $total= intval($m[1]);


        $inicio='<script';$fim='</script>';
        preg_match_all("#".$inicio."(.*?)".$fim."#s", $html, $result);
        $posts_=array();
        if(isset($result[1][0])){
            foreach ($result[1] as $key => $value) {
                if(strpos("[".$value."]", "window._sharedData")){
                    $posts_[]=$value;
                }
            }
            // ESTRUTURA DOS LINKS: https://www.instagram.com/p/BUZrI3JFrGt/?taken-by=sibra_camisaria

            foreach ($posts_ as $key => $posts) {
                            //IMAGENS
                $inicio='"display_url":';$fim=',';
                preg_match_all("#".$inicio."(.*?)".$fim."#s", $posts, $result);
                $imgs=str_replace(' ','',str_replace('"', '', str_replace('\u0026', '&', $result[1])));

                if(!isset($imgs[0])) continue;
            //IMAGENS

            //CODIGOS
                $inicio='"shortcode":';$fim=',';
                preg_match_all("#".$inicio."(.*?)".$fim."#s", $posts, $result);
                $cods=str_replace(' ','',str_replace('"', '', $result[1]));
            //CODIGOS

            // CRIANDOS OS LINKS
                $links=implode('\./', $cods);
                $links=str_replace('\./', '\./https://www.instagram.com/p/', $links);
                $links=str_replace('\./', '/?taken-by='.$instagram.'\./', $links);
                $links=explode('\./', $links);
                $links[0]='https://www.instagram.com/p/'.$links[0];
                $links[count($links)-1]=end($links).'/?taken-by='.$instagram;
            // CRIANDOS OS LINKS

                if(!isset($retorno['cods'])) $retorno['cods']=$cods;
                else $retorno['cods']=array_merge($cods, $retorno['cods']);

                if(!isset($retorno['links'])) $retorno['links']=$links;
                else $retorno['links']=array_merge($links, $retorno['links']);

                if(!isset($retorno['imgs'])) $retorno['imgs']=$imgs;
                else $retorno['imgs']=array_merge($imgs, $retorno['imgs']);

            }

            $retorno['itens']=array();
            foreach ($retorno['imgs'] as $key => $value) {
                $retorno['itens'][$key]['link']=$retorno['links'][$key];
                $retorno['itens'][$key]['imagem']=$value;
                $retorno['itens'][$key]['codigo']=$retorno['cods'][$key];
            }

            if ($settings->cache !== "0") {
                Cache::pull('lyra_hashtag');
                Cache::add('lyra_hashtag', $retorno, $settings->cache);
            }

            return $retorno;
        }return '';
    }
// ULTIMOS POSTS INSTAGRAM

}
