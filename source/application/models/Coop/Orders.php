<?
class Coop_Orders extends Awsome_DbTable 
{
	const STATUS_UNPAYED = "unpayed";
	const STATUS_PAYED = "payed";
	
	public function __construct()
	{
		parent::__construct();
		$this->tableName = "orders";
		$this->editableColumns = array("order_date", "user_id", "order_last_edit", "order_status", "order_reset_day");
		$this->nameColumn = "order_id";
		$this->primaryColumn = "order_id";
		$this->deleteColumn = "order_deleted";
		$this->orderBy = "order_id DESC";
	}

    public function getAllOrders($coop_id)
    {
        $sql = "SELECT * FROM orders WHERE coop_id = " . (int)$coop_id;
        if (!$results = $this->adapter->fetchAll($sql))
        {
            return false;
        }
        return $results;
    }

    public function getUserOrders($user_id)
    {
        $sql = "SELECT * FROM orders WHERE user_id = " . (int)$user_id;
        if (!$results = $this->adapter->fetchAll($sql))
        {
            return false;
        }
        return $results;
    }

    public function getAllPossibleResetDays($coop_id)
	{
		$sql = "select orders.order_reset_day from orders, users 
				where orders.user_id = users.user_id 
				and users.coop_id = " . $coop_id  . " 
				group by orders.order_reset_day
				order by orders.order_reset_day desc";
 		return $this->adapter->fetchAll($sql); 
	}
	
	public function countWeeklyOrdersForProduct($coop_id, $product_id, $reset_day = "")
	{
		if ($reset_day == "")
		{
			$coop_coops = new Coop_Coops();
			$coop = $coop_coops->getCoop($coop_id);
			$reset_day = $coop['coop_last_reset_day'];			
		}
  
		$sql = "select sum(oi.item_amount) as total 
				from orders o, order_items oi 
				where o.order_id = oi.order_id 
				and o.order_reset_day = '$reset_day'
				and oi.product_id = $product_id 
				group by oi.product_id";
				
		$row = $this->adapter->fetchRow($sql);
		return $row['total'];
	}
	
	public function getCurrentOrder($user_id)
	{
            $coop_users = new Coop_Users();
            
            $user = $coop_users->getUserOrNull($user_id);
			
			if (!$user) {
				return;
			}

            $coop_coops = new Coop_Coops();
            $coop = $coop_coops->getCoop($user['coop_id']);
            $reset_day = $coop['coop_last_reset_day'];


            $sql = "SELECT * FROM orders 
                            WHERE order_deleted = 0 
                            AND user_id = " . (int)$user_id . " 
                            AND order_reset_day = '$reset_day'";

            if (!$order = $this->adapter->fetchRow($sql))
            {
                    return false;
            }
            
            return $order;
	}
        
        //this function is not in use
        public function getUsersLastOrder($user_id)
        {
            $order = $this->getCurrentOrder($user_id);
            if (!$order)
            {
                return false;
            }

            $items = $this->getItems($order['order_id']);
            $categories = array();
            foreach ($items as $item)
            {
                $category_id = $item['category_id'];
                if (!isset($categories[$category_id]))
                {
                    $categories[$category_id] = array("category_name" => $item['category_name'],
                                                        "products" => array());

                }
                $categories[$category_id]['products'][$item['product_id']] = 
                        array("name" => $item['product_name'],
                            "price" => $item['product_price'],
                            "measure" => $item['product_measure'],
                            "amount" => $item['item_amount'],
                            "manufacturer" => $item['product_manufacturer'],
                            "description" => $item['product_description']);
            }
            
            return $categories;
        }

        public function createCurrentOrder($user_id)
	{
		$coop_users = new Coop_Users();
		$user = $coop_users->getUser($user_id);
		
		$coop_coops = new Coop_Coops();
		$coop = $coop_coops->getCoop($user['coop_id']);
		$reset_day = $coop['coop_last_reset_day'];
		
		
		$order_data = array();
		$order_data['order_deleted'] = 0;
		$order_data['order_date'] = date("Y-m-d");
		$order_data['user_id'] = $user_id;
		$order_data['order_last_edit'] = date("Y-m-d H:i:s");
		$order_data['order_status'] = self::STATUS_UNPAYED;
		$order_data['order_reset_day'] = $reset_day;
		
		$pk = $this->addOrder($order_data);
		return $pk;
	}
	
	public function getUserPreviousOrders($user_id)
	{
		$sql = "SELECT * FROM orders 
				WHERE order_deleted = 0 
				AND user_id = " . (int)$user_id . " 
				AND order_date < STR_TO_DATE(concat(year(curdate()), week(curdate()), ' Sunday'), '%X%V %W') 
				ORDER BY order_id DESC";
		if (!$order = $this->adapter->fetchAll($sql))
		{
			return false;
		}
		return $order;		
	}

	public function getOrdersOfResetDay($coop_id,$reset_date)
	{
		error_log('inside Orders::getOrdersOfResetDay');
		
		
        $sql = "SELECT u.user_id , u.user_first_name , u.user_last_name, u.user_phone, o.order_id, o.order_last_edit
                        FROM  users u , orders o
                        WHERE u.coop_id = " . (int)$coop_id . " 
                        AND o.user_id = u.user_id 
                        AND o.order_reset_day = '$reset_date'
                        ORDER BY u.user_first_name";
                
        if (!$orders = $this->adapter->fetchAll($sql))
		{
            return false;
		}
		foreach ($orders as $key => $value)
       	{
       		error_log("key is $key");
	        
	        if ($orders[$key])
	        {
	            $orders[$key]['total'] = $this->getOrderTotal($orders[$key]);
	            error_log("$key:  total is : " . $orders[$key]['total']);
	        }
    	}
		return $orders;
	}

	public function getOrdersResetDays($coop_id)
	{
		error_log('inside Orders::getOrdersResetDays');
		
		
        $sql = "SELECT order_reset_day
                        FROM  users u , orders o
                        WHERE u.coop_id = " . (int)$coop_id . " 
                        AND o.user_id = u.user_id 
                        GROUP BY o.order_reset_day
                        ORDER BY o.order_reset_day DESC";
                
        if (!$reset_days = $this->adapter->fetchAll($sql))
		{
            return false;
		}
		return $reset_days;
	}

	public function calcOrderTotal($order)
	{
		$total = 0;
	    $items = $this->getItems($order['order_id']);
				
		if (!empty($items)) {
	        foreach ($items as $item)
	        {
	            $total += ($item['item_amount'] * $item['product_price']);
		    }
		}
		return $total;
	}

	public function getOrderTotal($order)
	{
		if($order['total'])
		{
			error_log('total exist');
			return $order['total'];
		}
		error_log('total NOT exist:');
		
		return $this->calcOrderTotal($order);
	}


	
	public function getAllThisWeekOrders($coop_id)
	{
		$coop_coops = new Coop_Coops();
		$coop = $coop_coops->getCoop($coop_id);
		$reset_day = $coop['coop_last_reset_day'];
		
                $sql = "SELECT u.*, o.*
                        FROM users u, orders o, order_items oi, products p
                        WHERE oi.order_id = o.order_id 
                        AND o.user_id = u.user_id 
                        AND oi.product_id = p.product_id 
                        AND order_deleted = 0 
                        AND order_reset_day = '$reset_day'			
                        AND u.coop_id = " . (int)$coop_id . " 
                        GROUP BY o.order_id 
                        ORDER BY u.user_first_name";
                
        if (!$orders = $this->adapter->fetchAll($sql))
		{
                    return false;
		}
		
        foreach ($orders as $key => $value)
        {
            $current_order = $this->getCurrentOrder($value['user_id']);
            if ($current_order)
            {
                $orders[$key]['total'] = $this->getOrderTotal($current_order);
            }
        }
                  
		return $orders;
	}
        
        public function getOrdersGroupedByStatus($coop_id)
        {
            $orders = $this->getAllThisWeekOrders($coop_id);
            
            $groups = array("unpayed" => array(), "payed" => array());
            if ($orders)
            {
                foreach ($orders as $key => $value)
                {
                    $groups[$value['order_status']][] = $value;
                }
            }
            
            return $groups;
        }


        public function getFarmerReportForThisWeek($coop_id)
	{
		$coop_coops = new Coop_Coops();
		$coop = $coop_coops->getCoop($coop_id);
		$reset_day = $coop['coop_last_reset_day'];
		
		$sql = "SELECT p.product_name, SUM(oi.item_amount) AS amount, p.product_measure, pc.* 
		FROM orders o, order_items oi, products p, product_categories AS pc 
		WHERE p.category_id = pc.category_id 
		AND oi.product_id = p.product_id 
		AND oi.order_id = o.order_id 
		AND o.order_deleted = 0 
		AND o.order_reset_day = '$reset_day'
		AND p.coop_id = " . (int)$coop_id . " 
		GROUP BY p.product_id 
		ORDER BY pc.category_list_position, p.product_name";	
		if (!$data = $this->adapter->fetchAll($sql))
		{
			return false;
		}
		return $data;
	}
	
	
	public function getItems($order_id)
	{
                $sql = "SELECT *
                        FROM order_items AS oi, products AS p, product_categories AS pc
                        WHERE oi.product_id = p.product_id 
                        AND p.category_id = pc.category_id 
                        AND oi.item_deleted = 0 
                        AND oi.order_id = " . (int)$order_id . "
                        ORDER BY p.product_manufacturer";
		if (!$items = $this->adapter->fetchAll($sql))
		{
			return false;
		}
		$returned = array();
		foreach ($items as $num => $row)
		{
			$returned[$row['product_id']] = $row;
		}
		return $returned;
	}
	

	public function getItemsOfPrevOrder($order_id)
	{
		$order_date_sql = "SELECT order_date FROM orders where order_id = " . (int)$order_id;
		//$order_date_sql = "SELECT order_reset_day + 6 FROM orders where order_id = " . (int)$order_id;

		//SELECT order_reset_day + 6 FROM orders where order_id = 29941//this did not work
		$order_date = $this->adapter->fetchAll($order_date_sql);
		if( sizeof($order_date) != 1)
		{
			return false;
		}
		$date = $order_date[0]['order_date'];
		//error_log("Orders.php: getItems: dafna: order_date:");
		//error_log(print_r($date,TRUE));

		$min_date = $this->adapter->fetchAll("select MIN(price_date) from prices");
		$sql = "";
		error_log(print_r($min_date,TRUE));
		if(strtotime($date) >= strtotime($min_date[0]['MIN(price_date)'])){
			$product_columns = "product_about, product_description, product_image, product_items_left, product_manufacturer, product_measure, product_name";

			$sql = "SELECT product_id, $product_columns, price_amount as product_price, item_amount, round(item_amount * price_amount * 100) / 100 item_total FROM (SELECT products.product_id, $product_columns, category_id, item_amount, price_amount FROM (SELECT order_items.product_id, $product_columns, category_id, item_amount FROM (SELECT product_id, category_id, $product_columns from products) AS products join (SELECT product_id, item_amount FROM order_items where order_id = ".(int)$order_id.") as order_items on order_items.product_id = products.product_id) as products Join (SELECT det.product_id, det_id, price_amount FROM (SELECT product_id, max( price_id ) as det_id FROM prices WHERE price_date <= '$date' GROUP BY product_id) AS det JOIN ( SELECT product_id, price_id, price_amount FROM prices) AS val ON det.product_id = val.product_id and det.det_id = val.price_id) as prices on products.product_id = prices.product_id) as master  order by category_id, product_name";
        
		}
		else{
			$items = $this->getItems($order_id);
			
			foreach ($items as $product_id => $row)
			{
				$items[$product_id]['product_price'] = 0;
			}
			return array(TRUE, $items);
		}
		
		if (!$items = $this->adapter->fetchAll($sql))
		{
			error_log("Orders.php: getItems: dafna: fatchAll sql FAILED");
			return false;
		}
		$returned = array();

		error_log($sql);
		//error_log(print_r($items,TRUE));
		
		foreach ($items as $num => $row)
		{
			$returned[$row['product_id']] = $row;
		}
		
		return array(FALSE, $items);
	}
	
	public function getItemsByCategory($order_id)
	{
                $sql = "SELECT *
                        FROM order_items AS oi, products AS p, product_categories AS pc
                        WHERE oi.product_id = p.product_id 
                        AND p.category_id = pc.category_id 
                        AND oi.item_deleted = 0 
                        AND oi.order_id = " . (int)$order_id . "
                        ORDER BY p.category_id, p.product_name";
		if (!$items = $this->adapter->fetchAll($sql))
		{
			return false;
		}
		$returned = array();
		foreach ($items as $num => $row)
		{
			$returned[$row['product_id']] = $row;
		}
		return $returned;
	}
	
	public function getItemsForPrint($coop_id)
	{
		$orders = $this->getAllThisWeekOrders($coop_id);
		if ($orders != null)
		{
	    	foreach ($orders as $key => $order)
    		{
	    		$orders[$key]['items'] = $items = $this->getItemsByCategory($order['order_id']);
		    	$total = 0;
	    		foreach ($items as $item_key => $item)
	    		{
	    			$total += $orders[$key]['items'][$item_key]['cost'] = (float)($item['item_amount'] * $item['product_price']);
	    			
	    		}
	    		$orders[$key]['total'] = $total;
	    	}			
		}
		return $orders;
	}
	
	public function getWeekSummary($reset_day, $coop_id)
	{	
		$sql = "SELECT p.*, SUM(oi.item_amount) AS weekly_order, c.* 
		FROM products p, orders o, order_items oi, product_categories c 
		WHERE o.order_id = oi.order_id 
		AND oi.product_id = p.product_id 
		AND p.category_id = c.category_id 
		AND o.order_reset_day = '$reset_day'
		AND o.order_deleted = 0
		AND p.coop_id = " . (int)$coop_id . " 
		GROUP BY p.product_id 
		ORDER BY c.category_id, p.product_name";
				
		if (!$report = $this->adapter->fetchAll($sql))
		{
			return false;
		}
		return $report;
	}
	
	public function getOrdersOfProduct($reset_day, $product_id)
	{
		$sql = "SELECT u.*, 
		o.*, 
		SUM( p.product_price  * oi.item_amount) AS order_amount, 
		oi.* FROM users u, orders o, order_items oi, products p 
		WHERE oi.order_id = o.order_id 
		AND o.user_id = u.user_id 
		AND oi.product_id = p.product_id 
		AND order_deleted = 0 
		AND o.order_reset_day = '$reset_day'
		AND p.product_id = $product_id 
		GROUP BY o.order_id";		
		
		if (!$report = $this->adapter->fetchAll($sql))
		{
			return false;
		}
		return $report;
	}	
        
        public function getPayments($coop_id, $reset_day)
        {
           $sql = "SELECT pc.category_id, p.`product_price` * SUM(oi.item_amount) AS payments
            FROM orders o, order_items oi, products p, product_categories AS pc 
            WHERE p.category_id = pc.category_id 
            AND oi.product_id = p.product_id 
            AND oi.order_id = o.order_id 
            AND o.order_reset_day = '$reset_day'
            AND p.coop_id = " . (int)$coop_id . " 
            GROUP BY pc.category_id;";
            
            if (!$data = $this->adapter->fetchAll($sql))
            {
                    return false;
            }
            
            $result = array();
            foreach ($data as $row)
            {
                $result[$row['category_id']] = $row['payments'];
            }
            
            return $result;
        }
	
	public function getOrder($id)
	{
		$sql = "SELECT * FROM users u, orders o WHERE o.user_id = u.user_id AND order_deleted = 0 AND order_id = " . (int)$id;
		if (!$order = $this->adapter->fetchRow($sql))
		{
			return false;
		}
		return $order;
	}
	
	public function addOrder($data)
	{
		return $this->add($data);
	}
	
	public function editOrder($id, $data)
	{
		return $this->edit($id, $data);
	}
	
	public function deleteOrder($id)
	{
		return $this->delete($id);
	}
}