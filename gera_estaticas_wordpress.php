<?php
/*
Plugin Name: Gerador de estaticas Worpress JC
Plugin URL: https://github.com/ebelmiro/gera_estaticas_wordpress
Description: Gerador de estáticas para wordpress, diminuindo assim o uso de banco de dados da aplicação, evitando o caos e a destruição.

Author: Eudes Belmiro
Version: 1.0
*/
date_default_timezone_set('America/Recife');
$nome = str_replace('http://'.$_SERVER['HTTP_HOST'].'/', '', get_bloginfo('url'));
define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', ABSPATH);
define('BASE_DIR_MOBILE', ABSPATH . $nome.'_mobile');
define('TEM_MOBILE', is_dir(get_theme_root() . DS . get_template() . '_mobile') ? true : false);

define('POST_POR_PAGINA_GERADOR', 10);

Class GeraEstaticos
{
	public function __construct()
	{
		//$this->geraHome();
	}
	
	private function geraHome()
	{		
		$permalink = get_site_url() . '/geraindex/';		
		$pagina = $this->replaceLinks(file_get_contents($permalink));
		$dir = $this->criarDiretorio(BASE_DIR, '/');
		$pagina.='<!-- Gerado em '.date('d/m/Y H:i:s').' -->';

		$file= fopen($dir . 'index.htm', 'w+');
		fwrite($file, $pagina);
		fclose($file);	
		
		/*****		Versão Mobile		*****/
		if(TEM_MOBILE)
		{
			$permalinkMob = get_site_url() . '?mobile_device&key=gerador';
			$paginaMob = $this->replaceLinks(file_get_contents($permalinkMob));
			$dirMob = $this->criarDiretorio(BASE_DIR_MOBILE, '/');
			$paginaMob.='<!-- Gerado em '.date('d/m/Y H:i:s').' -->';
			
			$fileMob= fopen($dirMob . 'index.htm', 'w+');
			fwrite($fileMob, $paginaMob);
			fclose($fileMob);	
		}
	}
	
	public function apagarEstaticaPostPagina($id)
	{
		$file = BASE_DIR . (str_replace(get_site_url(),'',get_permalink($id)));	
		if(file_exists($file . 'index.htm'))
		{
			@unlink($file . 'index.htm');
			@rmdir($file);
		}
		else if(is_dir($file))
		{
			@rmdir($file);
		}
		else
		{
			// não tem nada aqui, ignore ...
		}
		
		/*****		Versão Mobile		*****/
		if(TEM_MOBILE)
		{
			$fileMob = BASE_DIR_MOBILE . (str_replace(get_site_url(),'',get_permalink($id)));	
			if(file_exists($file . 'index.htm'))
			{
				@unlink($fileMob . 'index.htm');
				@rmdir($fileMob);
			}
			else if(is_dir($fileMob))
			{
				@rmdir($fileMob);
			}
			else
			{
				// não tem nada aqui, ignore ...
			}		
		}
		$this->geraHome();
	}
	
	public function gerarEstaticaPostPagina($id)
	{    
		$permalink = get_permalink($id);				
		if(strpos($permalink, 'geraindex') == false)
		{		
			$permalink = get_permalink($id);			
			$pagina = $this->replaceLinks(file_get_contents($permalink .'?key=gerador'));
			
			$dir = $this->criarDiretorio(BASE_DIR, str_replace(get_site_url(),'',$permalink));

			foreach(wp_get_post_categories($id) as $cat)
			{		
				$this->gerarEstaticaTaxonomy($cat, $cat, 'category');
			}

			foreach(wp_get_post_terms($id) as $term)
			{		
				$this->gerarEstaticaTaxonomy($term->term_taxonomy_id, $term->term_taxonomy_id, 'post_tag');
			}	
			
			$pagina.='<!-- Gerado em '.date('d/m/Y H:i:s').' -->';
			
			$file= fopen($dir . 'index.htm', 'w+');
			fwrite($file, $pagina);
			fclose($file);
			
			
			/*****		Versão Mobile		*****/
			if(TEM_MOBILE)
			{
				$permalinkMob = get_permalink($id);
				$paginaMob = $this->replaceLinks(file_get_contents($permalinkMob.'?mobile_device&key=gerador'));
				
				$dirMob = $this->criarDiretorio(BASE_DIR_MOBILE, str_replace(get_site_url(),'',$permalinkMob));

				foreach(wp_get_post_categories($id) as $cat)
				{		
					$this->gerarEstaticaTaxonomy($cat, $cat, 'category');
				}

				foreach(wp_get_post_terms($id) as $term)
				{		
					$this->gerarEstaticaTaxonomy($term->term_taxonomy_id, $term->term_taxonomy_id, 'post_tag');
				}	
				
				$paginaMob.='<!-- Gerado em '.date('d/m/Y H:i:s').' -->';
				
				$fileMob = fopen($dirMob . 'index.htm', 'w+');
				fwrite($fileMob, $paginaMob);
				fclose($fileMob);	
			}			
		}
		
		$this->geraHome();
		$this->geraEstaticaPaginacao();
	}
	
	public function gerarEstaticaTaxonomy($termo, $termoId, $taxonomy)
	{	
		$link = get_term_link(get_term_by('term_taxonomy_id', $termo, $taxonomy));	
		$pagina = $this->replaceLinks(file_get_contents($link . '?key=gerador'));
		$dir = $this->criarDiretorio(BASE_DIR, str_replace(get_site_url(),'',$link));			
		
		$pagina.='<!-- Gerado em '.date('d/m/Y H:i:s').' -->';
		
		$file= fopen($dir . 'index.htm', 'w+');
		fwrite($file, $pagina);
		fclose($file);
		
		/*****		Versão Mobile		*****/
		if(TEM_MOBILE)
		{
			$linkMob = get_term_link(get_term_by('term_taxonomy_id', $termo, $taxonomy));	
			$paginaMob = $this->replaceLinks(file_get_contents($linkMob.'?mobile_device&key=gerador'));
			$dirMob = $this->criarDiretorio(BASE_DIR_MOBILE, str_replace(get_site_url(),'',$linkMob));			
			
			$paginaMob.='<!-- Gerado em '.date('d/m/Y H:i:s').' -->';
			
			$fileMob= fopen($dirMob . 'index.htm', 'w+');
			fwrite($fileMob, $paginaMob);
			fclose($fileMob);
		}		
		
		$this->geraHome();
	}

	public function apagarEstaticaTaxonomy($termo, $termoId, $taxonomy, $objeto)
	{		
		$file = BASE_DIR . DS . get_taxonomy($objeto->taxonomy)->rewrite['slug'] . DS . $objeto->slug .DS;

		if(file_exists($file . 'index.htm'))
		{
			@unlink($file . 'index.htm');
			@rmdir($file);
		}
		else if(is_dir($file))
		{
			@rmdir($file);
		}
		else
		{
			// não tem nada aqui, ignore ...
		}
		
		/*****		Versão Mobile		*****/
		if(TEM_MOBILE)
		{
			$fileMob = BASE_DIR_MOBILE . DS . get_taxonomy($objeto->taxonomy)->rewrite['slug'] . DS . $objeto->slug .DS;

			if(file_exists($fileMob . 'index.htm'))
			{
				@unlink($fileMob . 'index.htm');
				@rmdir($fileMob);
			}
			else if(is_dir($fileMob))
			{
				@rmdir($fileMob);
			}
			else
			{
				// não tem nada aqui, ignore ...
			}	
		}		
		
		$this->geraHome();
	}
	
	private function geraEstaticaPaginacao()
	{
		$numPosts = round(wp_count_posts()->publish / POST_POR_PAGINA_GERADOR);
		$pages = ($numPosts >= 4) ? 4 : $numPosts;
		
		for($a=2;$a<=$pages;$a++)		
		{
			$permalink = get_site_url() . "/geraindex$a/";	
			$pagina = $this->replaceLinks(file_get_contents($permalink));
			$dir = $this->criarDiretorio(BASE_DIR,"/page/$a/");
			
			$pagina.='<!-- Gerado em '.date('d/m/Y H:i:s').' -->';
			
			$file = fopen($dir . 'index.htm', 'w+');
			fwrite($file, $pagina);
			fclose($file);	
			
			/*****		Versão Mobile		*****/
			if(TEM_MOBILE)
			{
				$permalinkMob = get_site_url() . "/geraindex$a/?mobile_device";	
				$paginaMob = $this->replaceLinks(file_get_contents($permalinkMob));
				$dirMob = $this->criarDiretorio(BASE_DIR_MOBILE, "/page/$a/");
				
				$pagina.='<!-- Gerado em '.date('d/m/Y H:i:s').' -->';
				
				$fileMob = fopen($dirMob . 'index.htm', 'w+');
				fwrite($fileMob, $paginaMob);
				fclose($fileMob);
			}			
		}
	}

	private function criarDiretorio($base, $dir)
	{
		if(!is_dir($base . $dir))
		{
			if(mkdir($base . $dir, 0777, true))
			{
				return $base . $dir;
			}
			
			$arrDir = explode('/', $dir);	
			$path = $base . DS;
			foreach($arrDir as $d)
			{
				if($d != '')
				{			
					$path.= $d . DS;
					if(!is_dir($path))
					{
						mkdir($path);
					}
				}
			}
			return $path;
		}
			
		return 	$base . $dir;
	}

	private function replaceLinks($conteudo)
	{	
		$saida = $conteudo;				
		$array = array('/geraindex4','/geraindex3','/geraindex2','/geraindex');
		for($a=0;$a<count($array);$a++)
		{
			$saida = str_replace(get_site_url() . $array[$a], get_site_url(), $saida);
		}		
	
		return $saida;
	}	
}
$gerador = new GeraEstaticos();
// Ao publicar ou editar (como visibilidade publica) um post gera o estatico
add_action('publish_post', array($gerador, 'gerarEstaticaPostPagina'), 10, 1);

// Ao publicar ou editar (como visibilidade publica) uma pagina gera o estatico
add_action('publish_page', array($gerador, 'gerarEstaticaPostPagina'), 10, 1);

// Gera estatica de post agendado
add_action('publish_future_post', array($gerador, 'gerarEstaticaPostPagina'), 10,1);

// Ao mandar o post para lixeira apaga o estatico
add_action('trashed_post', array($gerador, 'apagarEstaticaPostPagina'), 10,1);

// Ao restaurar o post da lixeira regera o estatico
add_action('untrash_post', array($gerador, 'gerarEstaticaPostPagina'), 10,1);

// Ao cria categoria/tag a estatica da mesma e gerada
add_action('create_term', array($gerador, 'gerarEstaticaTaxonomy'), 10,3);

// Ao editar categoria/tag a estatica da mesma e gerada
add_action('edit_term', array($gerador, 'gerarEstaticaTaxonomy'), 10,3);

// Ao apagar a categoria/tag apaga o estatico
add_action('delete_term', array($gerador, 'apagarEstaticaTaxonomy'), 10,4);

// Ao realizar alguam operação com comentário
//add_action('pre_comment_approved', array($gerador, 'geraEstaticaComentario'), 10, 2);

/**********************************************
OBSERVAÇÕES:
Inserir no HTACESS
//Necessário para que o gerador não de um file_get em um arquivo estatico ja existente.

RewriteCond %{REQUEST_FILENAME} -d
RewriteCond %{QUERY_STRING} key=gerador [OR]
RewriteCond %{QUERY_STRING} dinamico=true
RewriteRule ^(.*)$ /index.php?%{QUERY_STRING} [L]
**********************************************/
?>