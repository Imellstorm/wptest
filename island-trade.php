<?php
/**
 * Island trade
 *
 * @package     Island trade
 * @author      Alex Karev
 * @copyright   2022 Alex Karev
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Island trade
 * Description: Island trade REST API.
 * Version:     1.0
 * Author:      Alex Karev
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

class Island_trade_API
{
    public function __construct()
    {
        $this->namespace = '/island_trade/v1';

        //Since CRUD is not required, I've added trade items as an array. Can be moved in DB.
        $this->trade_items = array(
            array('name'=>'Water','price' => 1),
            array('name'=>'Shirt','price' => 3),
            array('name'=>'Pants','price' => 4),
            array('name'=>'Dog','price' => 5),
            array('name'=>'Soup','price' => 8),
            array('name'=>'BE developer','price' => 10),
        );
    }

    /**
     * Register our routes.
     */
    public function register_routes() 
    {
        register_rest_route( $this->namespace, '/users_list', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_users' ),
                'permission_callback' => array( $this, 'get_permissions_check' ),        
            ),
        ) );

        register_rest_route( $this->namespace, '/create_user', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'create_user' ),
                'permission_callback' => array( $this, 'get_permissions_check' ), 
                'args' => array(
                    'user_name' => array(
                        'description'       => 'Default user pass = Test_pass',
                        'type'              => 'string',
                        'required'          => true,
                    )
                ),      
            ),
        ) );

        register_rest_route( $this->namespace, '/update_user_name', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'update_user_name' ),
                'permission_callback' => array( $this, 'get_permissions_check' ), 
                'args' => array(
                    'current_user_name' => array(
                        'description'       => 'Current user name',
                        'type'              => 'string',
                        'required'          => true,
                    ),
                    'new_user_name' => array(
                        'description'       => 'New user name',
                        'type'              => 'string',
                        'required'          => true,
                    )
                ),      
            ),
        ) );

        register_rest_route( $this->namespace, '/generate_items', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'generate_items' ),
                'permission_callback' => array( $this, 'get_permissions_check' ), 
                'args' => array(
                    'user_name' => array(
                        'description'       => 'User can generate items only once',
                        'type'              => 'string',
                        'required'          => true,
                    ),
                ),      
            ),
        ) );

        register_rest_route( $this->namespace, '/get_user_items', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_user_items' ),
                'permission_callback' => array( $this, 'get_permissions_check' ), 
                'args' => array(
                    'user_name' => array(
                        'description'       => 'Get User trade items',
                        'type'              => 'string',
                        'required'          => true,
                    ),
                ),      
            ),
        ) );

        register_rest_route( $this->namespace, '/make_bid', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'make_bid' ),
                'permission_callback' => array( $this, 'get_permissions_check' ), 
                'args' => array(
                    'user_name' => array(
                        'description'       => 'User name',
                        'type'              => 'string',
                        'required'          => true,
                    ),
                    'bid_items' => array(
                        'description'       => 'Enter user items and amount to the bid (JSON) e.g. [{"name":"Water","count":2},{"name":"Dog","count":1}]',
                        'type'              => 'string',
                        'required'          => true,
                    ),
                ),      
            ),
        ) );

        register_rest_route( $this->namespace, '/get_bids', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_bids' ),
                'permission_callback' => array( $this, 'get_permissions_check' ),        
            ),
        ) );

        register_rest_route( $this->namespace, '/trade', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'trade' ),
                'permission_callback' => array( $this, 'get_permissions_check' ), 
                'args' => array(
                    'bid_user_name' => array(
                        'description'       => 'Bid user name',
                        'type'              => 'string',
                        'required'          => true,
                    ),
                    'trade_user_name' => array(
                        'description'       => 'Trade user name',
                        'type'              => 'string',
                        'required'          => true,
                    ),
                    'items' => array(
                        'description'       => 'Enter user items and amount to the trade (JSON) e.g. [{"name":"Water","count":2},{"name":"Dog","count":1}]',
                        'type'              => 'string',
                        'required'          => true,
                    ),
                ),      
            ),
        ) );
    }

    /**
     * Check permissions for the endpoint. (Always true in our case)
     */
    public function get_permissions_check() 
    {
        return true;
    }

    /**
     * Get users list
     */
    public function get_users() 
    {
        $users = get_users();

        $result = array();

        foreach($users as $user)
        {
            $result[]['user_login'] = $user->user_login;
        }

        return new WP_REST_Response(
            array(
                'code' => 200,
                'message' => 'User list',
                'data'  => $result,
            )
        );
    }

    /**
     * Create user
     *
     * @param WP_REST_Request $request Current request.
     */
    public function create_user( WP_REST_Request $request ) 
    {
        $user_name = $request->get_param('user_name');

        if( username_exists( $user_name ) )
        {
            return new WP_Error( 409, 'User already exists', array( 'status' => 409 ) );
        }

        wp_create_user( $user_name, 'Test_pass', $user_name.'@devpoint.me' );

        return new WP_REST_Response(
            array(
                'code' => 200,
                'message' => 'User created',
            )
        );
    }

    /**
     * Update user name
     *
     * @param WP_REST_Request $request Current request.
     */
    public function update_user_name( WP_REST_Request $request ) 
    {
        $current_user_name = $request->get_param('current_user_name');
        $new_user_name = $request->get_param('new_user_name');
        $user = get_user_by( 'login', $current_user_name );

        if( !$user )
        {
            return new WP_Error( 404, 'User not found', array( 'status' => 404 ) );
        }

        if( username_exists( $new_user_name ) )
        {
            return new WP_Error( 409, 'Name already exists', array( 'status' => 409 ) );
        }

        //Used wpdb because wp_update_user ignores 'user_login' param
        global $wpdb;
        $wpdb->update(
            $wpdb->users, 
            ['user_login' => $new_user_name], 
            ['ID' => $user->ID]
        );

        return new WP_REST_Response(
            array(
                'code' => 200,
                'message' => 'User name updated',
            )
        );
    }

    /**
     * Generate user items
     *
     * @param WP_REST_Request $request Current request.
     */
    public function generate_items( WP_REST_Request $request ) 
    {
        $user = get_user_by( 'login', $request->get_param('user_name') );

        if( !$user )
        {
            return new WP_Error( 404, 'User not found', array( 'status' => 404 ) );
        }

        $trade_items_data = get_user_meta( $user->ID, 'trade_items_data', true );      

        if( $trade_items_data )
        {
            return new WP_Error( 403, 'User already created trade items list', array( 'status' => 403 ) );
        }

        $max_price_value = rand( 3,20 );        
        $user_items['total_price'] = 0;
        $repeat = true;

        while ( $repeat )
        {
            $random_item_index = rand( 0, count( $this->trade_items ) - 1 );
            $total_price = $user_items['total_price'] + $this->trade_items[$random_item_index]['price'];

            //Checking if we are not owerlapping max price
            if( $total_price <= $max_price_value )
            {
                $user_items['total_price'] = $total_price;
                $user_items['trade_items'][$random_item_index] = array(
                    'name'  => $this->trade_items[$random_item_index]['name'],
                    'price' => $this->trade_items[$random_item_index]['price'],
                    'count' => $user_items['trade_items'][$random_item_index]['count'] + 1
                ); 
            } else {
                //If min price not reached, one more try
                if( $user_items['total_price'] >= 3 )
                {
                    $repeat = false;
                }
            }
        }

        update_user_meta( $user->ID, 'trade_items_data', json_encode($user_items) );

        return new WP_REST_Response(
            array(
                'code' => 200,
                'message' => 'User trade items created',
                'data' => $user_items,
            )
        );
    }

    /**
     * Get user items
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_user_items( WP_REST_Request $request ) 
    {
        $user = get_user_by( 'login', $request->get_param('user_name') );

        if( !$user )
        {
            return new WP_Error( 404, 'User not found', array( 'status' => 404 ) );
        }

        $trade_items_data = get_user_meta( $user->ID, 'trade_items_data', true );

        if( !$trade_items_data )
        {
            return new WP_Error( 404, 'User has no trade items', array( 'status' => 404 ) );
        }

        return new WP_REST_Response(
            array(
                'code' => 200,
                'message' => 'User trade items',
                'data' => json_decode($trade_items_data),
            )
        );
    }

    /**
     * Make a bid
     *
     * @param WP_REST_Request $request Current request.
     */
    public function make_bid( WP_REST_Request $request ) 
    {
        $user = get_user_by( 'login', $request->get_param('user_name') );

        if( !$user )
        {
            return new WP_Error( 404, 'User not found', array( 'status' => 404 ) );
        }

        $user_bid = get_user_meta( $user->ID, 'bid', true );

        if( $user_bid )
        {
            return new WP_Error( 409, 'User already has a bid', array( 'status' => 409 ) );
        }

        $trade_items_data = get_user_meta( $user->ID, 'trade_items_data', true );

        if( !$trade_items_data )
        {
            return new WP_Error( 404, 'User has no trade items', array( 'status' => 404 ) );
        }

        $bid_items_json = $request->get_param('bid_items');

        if( !$this->json_validator( $bid_items_json ) )
        {
            return new WP_Error( 500, 'JSON data is not valid', array( 'status' => 500 ) );
        }       

        $bid_items = json_decode( $bid_items_json );
        $trade_items_data = json_decode( $trade_items_data );
        $user_items = $trade_items_data->trade_items;
        $bid['total_price'] = 0; 

        foreach( $bid_items as $bid_item )
        {
            $bid_item->price = 0;

            foreach( $user_items as $user_item )
            {           
                //Checking if user has an item to bid
                if( strtolower( $user_item->name ) == strtolower( $bid_item->name ) )
                {
                    //Checking if user has enough items to bid
                    if( $bid_item->count > $user_item->count )
                    {
                        return new WP_Error( 404, 'User has not enought items to bid', array( 'status' => 404 ) );
                    }

                    $bid_item->price = $user_item->price;
                }
            }

            if( !$bid_item->price )
            {
                return new WP_Error( 404, 'User has no requested items to bid', array( 'status' => 404 ) );
            } 

            $bid['trade_items'][] = $bid_item;
            $bid['total_price']+= $bid_item->count * $bid_item->price;            
        }

        update_user_meta( $user->ID, 'bid', json_encode( $bid ) );

        return new WP_REST_Response(
            array(
                'code' => 200,
                'message' => 'Bid created',
                'data' => $bid,
            )
        );
    }

    /**
     * Get all bids
     */
    public function get_bids() 
    {
        $users = get_users();

        $result = array();

        foreach($users as $key=>$user)
        {
            $user_bid = get_user_meta( $user->ID, 'bid', true );
            if( $user_bid )
            {
                $result[$key]['user_login'] = $user->user_login;
                $result[$key]['bid'] = json_decode( $user_bid );
            }
        }

        return new WP_REST_Response(
            array(
                'code' => 200,
                'message' => 'Bids list',
                'data'  => $result,
            )
        );
    }

    /**
     * Trade
     *
     * @param WP_REST_Request $request Current request.
     */
    public function trade( WP_REST_Request $request ) 
    {
        //Check users
        $bid_user = get_user_by( 'login', $request->get_param('bid_user_name') );

        if( !$bid_user )
        {
            return new WP_Error( 404, 'Bid user not found', array( 'status' => 404 ) );
        }

        $trade_user = get_user_by( 'login', $request->get_param('trade_user_name') );

        if( !$trade_user )
        {
            return new WP_Error( 404, 'Trade user not found', array( 'status' => 404 ) );
        }        

        //Check bid
        $bid_json = get_user_meta( $bid_user->ID, 'bid', true );

        if( !$bid_json )
        {
            return new WP_Error( 404, 'User has no bid', array( 'status' => 404 ) );
        }

        $bid = json_decode($bid_json, true);

        //Check if trade user has a bid - user can't make a trade if he has opened bid (disallows to change same items twice)
        $trade_user_bid_json = get_user_meta( $trade_user->ID, 'bid', true );

        if( $trade_user_bid_json )
        {
            return new WP_Error( 403, 'trade user can\'t make a trade because he has opened bids', array( 'status' => 403 ) );
        }

        //Check trade user items
        $trade_user_items_data = get_user_meta( $trade_user->ID, 'trade_items_data', true );

        if( !$trade_user_items_data )
        {
            return new WP_Error( 404, 'Trade user has no trade items', array( 'status' => 404 ) );
        }

        $trade_user_items_data = json_decode( $trade_user_items_data, true );

        //Check bid user items
        $bid_user_items_data = get_user_meta( $bid_user->ID, 'trade_items_data', true );

        if( !$bid_user_items_data )
        {
            return new WP_Error( 404, 'Bid user has no trade items', array( 'status' => 404 ) );
        }

        $bid_user_items_data = json_decode( $bid_user_items_data, true );

        //Check trade items
        $items_json = $request->get_param('items');

        if( !$this->json_validator( $items_json ) )
        {
            return new WP_Error( 500, 'JSON data is not valid', array( 'status' => 500 ) );
        }       

        $items = json_decode( $items_json, true );
        
        //Gather trade data
        $trade['total_price'] = 0; 

        foreach( $items as $item )
        {
            $item['price'] = 0;

            foreach( $trade_user_items_data['trade_items'] as $user_item )
            {           
                //Checking if user has an item to trade
                if( strtolower( $user_item['name'] ) == strtolower( $item['name'] ) )
                {
                    //Checking if user has enough items to trade
                    if( $item['count'] > $user_item['count'] )
                    {
                        return new WP_Error( 404, 'User has not enought items to trade', array( 'status' => 404 ) );
                    }

                    $item['price'] = $user_item['price'];
                }
            }

            if( !$item['price'] )
            {
                return new WP_Error( 404, 'User has no requested items to trade', array( 'status' => 404 ) );
            } 

            $trade['trade_items'][] = $item;
            $trade['total_price']+= $item['count'] * $item['price'];            
        }

        if( $bid['total_price'] > $trade['total_price'] )
        {
            return new WP_Error( 403, 'Trade price less than the bid price', array( 'status' => 403 ) );
        }

        //Update users with a trade items
        foreach( $trade['trade_items'] as $trade_item )
        {
            $bid_user_has_trade_item = false;

            //Add trade item to the bid user
            //Check if bid user already has trade item
            foreach( $bid_user_items_data['trade_items'] as $key => $bid_user_item )
            {
                if( strtolower( $trade_item['name'] ) == strtolower( $bid_user_item['name'] ) )
                {
                    $bid_user_items_data['trade_items'][$key]['count']+= $trade_item['count'];

                    $bid_user_has_trade_item = true;
                }
            }

            if( !$bid_user_has_trade_item )
            {
                $bid_user_items_data['trade_items'][] = $trade_item;
            }           

            $bid_user_items_data['total_price']+= $trade_item['price'] * $trade_item['count'];            

            //Remove trade items from the trade user
            foreach( $trade_user_items_data['trade_items'] as $key => $trade_user_item )
            {
                if( strtolower( $trade_item['name'] ) == strtolower( $trade_user_item['name'] ) )
                {
                    if( $trade_item['count'] == $trade_user_item['count'] )
                    {
                        unset( $trade_user_items_data['trade_items'][$key] );
                    } else {
                        $trade_user_items_data['trade_items'][$key]['count'] = $trade_user_item['count'] - $trade_item['count'];
                    }

                    $trade_user_items_data['total_price'] = $trade_user_items_data['total_price'] - $trade_item['price'] * $trade_item['count'];
                }
            }
        } 

        //Update users with a bid items
        foreach( $bid['trade_items'] as $bid_item )
        {
            $trade_user_has_bid_item = false;

            //Add bid item to the trade user
            //Check if trade user already has bid item
            foreach( $trade_user_items_data['trade_items'] as $key => $trade_user_item )
            {
                if( strtolower( $bid_item['name'] ) == strtolower( $trade_user_item['name'] ) )
                {
                    $trade_user_items_data['trade_items'][$key]['count']+= $bid_item['count'];

                    $trade_user_has_bid_item = true;
                }
            }

            if( !$trade_user_has_bid_item )
            {
                $trade_user_items_data['trade_items'][] = $bid_item;
            }

            $trade_user_items_data['total_price']+= $bid_item['count'] * $bid_item['price'];            

            //Remove bid items from the bid user
            foreach( $bid_user_items_data['trade_items'] as $key => $bid_user_item )
            {
                if( strtolower( $bid_item['name'] ) == strtolower( $bid_user_item['name'] ) )
                {
                    if( $bid_item['count'] == $bid_user_item['count'] )
                    {
                        unset( $bid_user_items_data['trade_items'][$key] );
                    } else {
                        $bid_user_items_data['trade_items'][$key]['count'] = $bid_user_item['count'] - $bid_item['count'];
                    }

                    $bid_user_items_data['total_price'] = $bid_user_items_data['total_price'] - $bid_item['price'] * $bid_item['count'];
                }
            }             
        }     
    
        update_user_meta( $trade_user->ID, 'trade_items_data', json_encode($trade_user_items_data) ); 
        update_user_meta( $bid_user->ID, 'trade_items_data', json_encode($bid_user_items_data) ); 
        update_user_meta( $bid_user->ID, 'bid', '' ); 

        return new WP_REST_Response(
            array(
                'code' => 200,
                'message' => 'Trade done',
                'data' => $trade,
            )
        );
    }

    /**
     * JSON Validator function
     *
     * @param string $data - JSON string.
     */
    private function json_validator($data) 
    {
        @json_decode($data);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}

/**
 * Function to register our routes from the controller.
 */
function prefix_register_island_trade_rest_routes() 
{
    $controller = new Island_trade_API();
    $controller->register_routes();
}
 
add_action( 'rest_api_init', 'prefix_register_island_trade_rest_routes' );
