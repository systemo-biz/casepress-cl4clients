<?php
/*
Plugin Name: CasePress. Выводит заказы на странице клиента
Description: Функции выводит на странице клиента все заказы. Клиент должен быть указан через поле ACF (relationship) с ключем client.
Version: 1.0
Author: Systemo
*/

function list_cases_for_client($content){

    if(is_singular(array('organizations', 'persons'))):
        $post = get_post();
    
        $items = get_posts(array(
							'post_type' => 'cases',
							'meta_query' => array(
								array(
									'key' => 'client', // name of custom field
									'value' => '"' . $post->ID . '"', // matches exaclty "123", not just 123. This prevents a match for "1234"
									'compare' => 'LIKE'
								)
							)
						));
    
        if($items):
            $url_list = add_query_arg( array('post_type'=>'cases','acf_client'=>$post->ID), get_site_url());
            ob_start();
            ?>    
                <section class="list_cases_for_client">
                    <header>
                        <h1>Заказы клиента</h1>
                    </header>
                    <ul>
                        <?php foreach( $items as $post ): setup_postdata($post);?>
                            <li>
                                <a href="<?php echo get_permalink( $post->ID ); ?>">
                                    <h2 class="entry-title"><?php echo get_the_title( $post->ID ); ?></h2>
                                </a>
                                <div>
                                    <ul class="list-inline">
                                        <?php do_action('case_meta_top_add_li'); ?>
                                    </ul> 
                                </div>
                            </li>
                        <?php endforeach; wp_reset_postdata(); ?>
                    </ul>
                    <footer>
                        <a href="<?php echo $url_list ?>" class='btn btn-default'>Все заказы</a>
                    </footer>
                </section>
            <?php
            $html = ob_get_contents();
             ob_get_clean();
             $content .= $html;
        endif;
    endif;
    
    return $content;
} add_filter('the_content', 'list_cases_for_client');


//добавляе возможность отбора постов через параметр урл case_members, который может содержать ИД персоны
// сейчас используется в досье Персноны, по возможности надо заменить на filter_posts_meta_cp (полный аналог, но более универсальный) и удалить всю данную функцию
function filter_case_client_acf( $query ) {
	
	if(! $query->is_main_query() ) return;
	if(empty($_REQUEST['acf_client'])) return;
    
	$acf_client = $_REQUEST['acf_client'];
    
	if($acf_client):
	
		//Get original meta query
		$meta_query = $query->get('meta_query');
		//Add our meta query to the original meta queries
		$meta_query[] = array(
                                        'key' => 'client', // name of custom field
                                        'value' => '"' . $acf_client . '"', // matches exaclty "123", not just 123. This prevents a match for "1234"
                                        'compare' => 'LIKE'
                                    );
		$query->set('meta_query',$meta_query);
		
	endif;
    
    return;
}
add_action( 'pre_get_posts', 'filter_case_client_acf' );