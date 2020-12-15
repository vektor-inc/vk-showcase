<?php
/**
 * VK Showcase Post Types
 *
 * @package VK Showcase
 */

/**
 * VK Showcase Post Types
 */
class VK_Showcase_Post_Types {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 0 );
		add_filter( 'wpcf7_map_meta_cap', array( __CLASS__, 'wpcf7_capabilities' ) );
		add_filter( 'post_type_labels_post', array( __CLASS__, 'custom_post_labels' ) );
	}


	/**
	 * 投稿のラベルを変更します。
	 */
	public static function custom_post_labels( $labels ) {
		$labels->name = '制作実績'; // 投稿
		$labels->singular_name = '制作実績'; // 投稿
		$labels->add_new = '新規追加'; // 新規追加
		$labels->add_new_item = '制作実績を追加'; // 新規投稿を追加
		$labels->edit_item = '投稿の編集'; // 投稿の編集
		$labels->new_item = '新規制作実績'; // 新規投稿
		$labels->view_item = '制作実績を表示'; // 投稿を表示
		$labels->search_items = '制作実績を検索'; // 投稿を検索
		$labels->not_found = '制作実績が見つかりませんでした。'; // 投稿が見つかりませんでした。
		$labels->not_found_in_trash = 'ゴミ箱内に制作実績が見つかりませんでした。'; // ゴミ箱内に投稿が見つかりませんでした。
		$labels->parent_item_colon = ''; // (なし)
		$labels->all_items = '制作実績一覧'; // 投稿一覧
		$labels->archives = '制作実績アーカイブ'; // 投稿アーカイブ
		$labels->insert_into_item = '制作実績に挿入'; // 投稿に挿入
		$labels->uploaded_to_this_item = 'この制作実績へのアップロード'; // この投稿へのアップロード
		$labels->featured_image = 'アイキャッチ画像'; // アイキャッチ画像
		$labels->set_featured_image = 'アイキャッチ画像を設定'; // アイキャッチ画像を設定
		$labels->remove_featured_image = 'アイキャッチ画像を削除'; // アイキャッチ画像を削除
		$labels->use_featured_image = 'アイキャッチ画像として使用'; // アイキャッチ画像として使用
		$labels->filter_items_list = '制作実績リストの絞り込み'; // 投稿リストの絞り込み
		$labels->items_list_navigation = '制作実績リストナビゲーション'; // 投稿リストナビゲーション
		$labels->items_list = '制作実績リスト'; // 投稿リスト
		$labels->menu_name = '制作実績'; // 投稿
		$labels->name_admin_bar = '制作実績'; // 投稿
		return $labels;
	}


	/**
	 * Add Showcase Post Type
	 */
	public static function register_post_type() {

		register_post_type(
			'information',
			array(
				'label'        => __( 'お知らせ', 'vk-showcase' ),
				'public'       => true,
				'has_archive'  => true,
				'menu_icon'    => 'dashicons-screenoptions',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'editor' ),
				'map_meta_cap' => true,
				'capabilities' => array(
					'edit_posts'             => 'edit_pages',
					'edit_others_posts'      => 'edit_others_pages',
					'publish_posts'          => 'publish_pages',
					'read_private_posts'     => 'read_private_pages',
					'delete_posts'           => 'delete_pages',
					'delete_private_posts'   => 'delete_private_pages',
					'delete_published_posts' => 'delete_published_pages',
					'delete_others_posts'    => 'delete_others_pages',
					'edit_private_posts'     => 'edit_private_pages',
					'edit_published_posts'   => 'edit_published_pages',
				),
			)
		);

		register_taxonomy(
			'themes_and_skins',
			'post',
			array(
				'label'             => __( 'テーマ・スキン', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'site_type',
			'post',
			array(
				'label'             => __( 'サイト種別・機能', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'in_charge',
			'post',
			array(
				'label'             => __( '担当範囲', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'industry',
			'post',
			array(
				'label'             => __( '業種', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
			)
		);

		unregister_taxonomy_for_object_type( 'category', 'post' );
		unregister_taxonomy_for_object_type( 'post_tag', 'post' );
	}

	public static function wpcf7_capabilities( $meta_caps ) {
		$meta_caps = array(
			'wpcf7_edit_contact_form'    => 'edit_pages',
			'wpcf7_edit_contact_forms'   => 'edit_pages',
			'wpcf7_read_contact_form'    => 'edit_pages',
			'wpcf7_read_contact_forms'   => 'edit_pages',
			'wpcf7_delete_contact_form'  => 'delete_pages',
			'wpcf7_delete_contact_forms' => 'delete_pages',
			'wpcf7_manage_integration'   => 'manage_options',
			'wpcf7_submit'               => 'read',
		);

		return $meta_caps;
	}

}

new VK_Showcase_Post_Types();
