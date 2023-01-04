<?php
/**
 * Friends Feed Item
 *
 * This contains the template for a feed item.
 *
 * @package Friends
 */

namespace Friends;

/**
/**
 * This class describes a friends feed item.
 */
class Feed_Item {
	public $title;
	public $permalink;
	public $gravatar;
	public $post_title;
	public $author;
	public $post_content;
	public $content;
	public $comment_count;
	public $comments_feed;
	public $post_id;
	public $post_format;
	public $post_status;
	public $date;
	public $_feed_rule_delete;
	public $_feed_rule_transform;
	public $_is_new;
	public $_full_content_fetched;
	public function __construct( $data = array() ) {
		foreach ( $data as $key => $value ) {
			$this->$key = $value;
		}
	}
}
