<?php
// Creates the database, tables (database/schema.sql) and realistic sample data.
function install_database(): string {
    $c = cfg();
    $pdo = new PDO("mysql:host={$c['host']};port={$c['port']};charset=utf8mb4", $c['user'], $c['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$c['name']}` CHARACTER SET utf8mb4");
    $pdo->exec("USE `{$c['name']}`");
    foreach (array_filter(array_map('trim', explode(';', file_get_contents(__DIR__ . '/../database/schema.sql')))) as $sql) $pdo->exec($sql);
    mt_srand(2026);
    $today = new DateTime('today');
    $d = fn($n) => (clone $today)->modify(($n >= 0 ? '+' : '') . "$n days")->format('Y-m-d');

    $pdo->beginTransaction();
    $ins = fn($sql) => $pdo->prepare($sql);
    $st = $ins('INSERT INTO users(full_name,username,password_hash,role) VALUES(?,?,?,?)');
    foreach ([['Admin Owner','admin','admin123','admin'],['Florence Manager','manager','manager123','manager'],['Daniel Cashier','cashier','cashier123','cashier'],['Agnes Cashier','agnes','cashier123','cashier']] as $u)
        $st->execute([$u[0], $u[1], password_hash($u[2], PASSWORD_DEFAULT), $u[3]]);

    $cats = ['Groceries','Fresh Produce','Beverages','Dairy & Eggs','Household','Personal Care','Bakery'];
    $st = $ins('INSERT INTO categories(name) VALUES(?)'); foreach ($cats as $c1) $st->execute([$c1]);
    $st = $ins('INSERT INTO suppliers(name,contact_person,phone,email,address) VALUES(?,?,?,?,?)');
    foreach ([['Kakira Distributors','Sarah Nakato','0772 100 201','orders@kakiradist.ug','Jinja Road, Kampala'],
              ['Fresh Farms Uganda','Moses Okello','0701 220 302','sales@freshfarms.ug','Masaka Road, Kampala'],
              ['Nile Beverages Ltd','Ritah Namuli','0752 330 403','supply@nilebev.ug','Industrial Area, Kampala'],
              ['Mukwano Household Supplies','Ibrahim Kato','0782 440 504','info@mukwanohs.ug','Kyambogo, Kampala'],
              ['Dairy Direct Mbarara','Grace Atuhaire','0703 550 605','grace@dairydirect.ug','Mbarara City']] as $s) $st->execute($s);

    // sku, name, category, supplier, cost, price, stock, reorder level, days to expiry (null = none)
    $products = [
     ['GR001','Rice 1kg','Groceries',1,4200,5000,120,40,400],['GR002','Sugar 1kg','Groceries',1,3800,4500,150,50,500],
     ['GR003','Maize Flour (Posho) 2kg','Groceries',1,5200,6200,90,30,300],['GR004','Cooking Oil 3L','Groceries',1,18500,21500,45,20,365],
     ['GR005','Salt 500g','Groceries',1,900,1300,200,40,700],['GR006','Spaghetti 500g','Groceries',1,2300,3000,8,25,450],
     ['GR007','Beans 1kg','Groceries',2,4500,5500,70,30,300],['GR008','Wheat Flour 2kg','Groceries',1,6000,7200,60,25,250],
     ['GR009','Tomato Paste 400g','Groceries',1,3100,4000,40,15,500],['GR010','Blue Band 500g','Groceries',4,7800,9500,35,15,200],
     ['FP001','Tomatoes 1kg','Fresh Produce',2,2200,3000,40,25,5],['FP002','Onions 1kg','Fresh Produce',2,2500,3500,55,25,20],
     ['FP003','Irish Potatoes 1kg','Fresh Produce',2,2000,3000,6,30,14],['FP004','Matooke (bunch)','Fresh Produce',2,9000,13000,25,15,6],
     ['FP005','Bananas Sweet 1kg','Fresh Produce',2,2800,4000,30,15,5],['FP006','Cabbage (each)','Fresh Produce',2,1500,2500,28,12,9],
     ['FP007','Pineapple (each)','Fresh Produce',2,2500,4000,18,10,8],['BV001','Mineral Water 1.5L','Beverages',3,1100,1800,160,60,365],
     ['BV002','Soda 500ml','Beverages',3,1300,2000,140,60,300],['BV003','Mango Juice 1L','Beverages',3,3500,4800,50,20,180],
     ['BV004','Tea Leaves 100g','Beverages',3,1800,2500,5,20,500],['BV005','Instant Coffee 100g','Beverages',3,9000,12000,22,10,600],
     ['DE001','Fresh Milk 1L','Dairy & Eggs',5,2600,3500,70,40,6],['DE002','Yoghurt 500ml','Dairy & Eggs',5,3000,4200,45,20,14],
     ['DE003','Eggs (tray of 30)','Dairy & Eggs',2,11500,14000,24,12,18],['DE004','Cheese 250g','Dairy & Eggs',5,8500,11000,15,8,25],
     ['DE005','Butter 250g','Dairy & Eggs',5,6500,8500,9,10,12],['HH001','Laundry Soap Bar','Household',4,2000,2800,100,40,null],
     ['HH002','Washing Powder 1kg','Household',4,6500,8000,48,20,null],['HH003','Dish Wash Liquid 750ml','Household',4,4000,5500,36,15,null],
     ['HH004','Toilet Paper (4 rolls)','Household',4,3800,5000,80,30,null],['HH005','Bleach 1L','Household',4,3200,4500,4,15,null],
     ['PC001','Toothpaste 100g','Personal Care',4,3200,4500,60,20,800],['PC002','Bathing Soap','Personal Care',4,1800,2500,110,40,700],
     ['PC003','Body Lotion 400ml','Personal Care',4,9000,12500,25,10,600],['PC004','Sanitary Pads (pack)','Personal Care',4,3500,4800,50,20,900],
     ['BK001','Bread (sliced loaf)','Bakery',5,3200,4000,50,40,4],['BK002','Mandazi (pack of 6)','Bakery',5,2200,3000,30,20,3],
     ['BK003','Cake Slice','Bakery',5,1800,2800,20,15,5],['BK004','Digestive Biscuits','Bakery',1,2500,3500,75,25,300],
    ];
    $st = $ins('INSERT INTO products(sku,name,category_id,supplier_id,cost_price,selling_price,stock_qty,reorder_level,expiry_date) VALUES(?,?,?,?,?,?,?,?,?)');
    foreach ($products as $p) $st->execute([$p[0], $p[1], array_search($p[2], $cats) + 1, $p[3], $p[4], $p[5], $p[6], $p[7], $p[8] === null ? null : $d($p[8])]);

    $names = ['Nakato Joan','Okello David','Namuli Ritah','Kato Ibrahim','Atuhaire Grace','Mugisha Peter','Auma Esther','Ssemakula Brian','Nabirye Susan','Tumusiime Allan','Achieng Mercy','Lubega Henry','Nansubuga Harriet','Opio Samuel','Babirye Lydia'];
    $st = $ins('INSERT INTO customers(full_name,phone,joined_on) VALUES(?,?,?)');
    foreach ($names as $i => $n) $st->execute([$n, '07' . [0,2,5,7,8][$i % 5] . (1000000 + $i * 531977 % 8999999), $d(-mt_rand(30, 300))]);

    $weights = []; foreach ($products as $i => $_) $weights[$i] = [1,1,2,3,5][mt_rand(0, 4)];
    $wsum = array_sum($weights);
    $pick = function () use ($weights, $wsum) { $r = mt_rand(1, $wsum); foreach ($weights as $i => $w) { if (($r -= $w) <= 0) return $i; } return 0; };
    $saleSt = $ins('INSERT INTO sales(sale_date,user_id,customer_id,total,payment_method,points_earned) VALUES(?,?,?,?,?,?)');
    $itemSt = $ins('INSERT INTO sale_items(sale_id,product_id,qty,unit_price,unit_cost) VALUES(?,?,?,?,?)');
    $ptsSt  = $ins('UPDATE customers SET loyalty_points=loyalty_points+? WHERE customer_id=?');
    $pay = ['Cash','Mobile Money','Mobile Money','Card'];
    for ($back = 120; $back >= 0; $back--) {
        $day = (clone $today)->modify("-$back days");
        $n = mt_rand(14, 30) + (in_array((int)$day->format('N'), [5, 6]) ? 8 : 0);
        for ($k = 0; $k < $n; $k++) {
            $ts = $day->format('Y-m-d') . sprintf(' %02d:%02d:%02d', mt_rand(8, 20), mt_rand(0, 59), mt_rand(0, 59));
            $cust = mt_rand(1, 100) <= 35 ? mt_rand(1, count($names)) : null;
            $lines = [];
            for ($j = mt_rand(1, 6); $j > 0; $j--) { $i = $pick(); $lines[$i] = ($lines[$i] ?? 0) + mt_rand(1, 3); }
            $total = 0; foreach ($lines as $i => $q) $total += $products[$i][5] * $q;
            $pts = $cust ? intdiv((int)$total, 1000) : 0;
            $saleSt->execute([$ts, [3,4,3][mt_rand(0, 2)], $cust, $total, $pay[mt_rand(0, 3)], $pts]);
            $sid = $pdo->lastInsertId();
            foreach ($lines as $i => $q) $itemSt->execute([$sid, $i + 1, $q, $products[$i][5], $products[$i][4]]);
            if ($cust) $ptsSt->execute([$pts, $cust]);
        }
    }
    $poSt = $ins('INSERT INTO purchase_orders(supplier_id,user_id,order_date,status,total_cost,received_date) VALUES(?,?,?,?,?,?)');
    $poi  = $ins('INSERT INTO po_items(po_id,product_id,qty,unit_cost) VALUES(?,?,?,?)');
    foreach ([1,2,3,4,5,1] as $k => $sup) {
        $od = $d(-mt_rand(5, 100)); $tot = 0; $rows = [];
        foreach ($products as $i => $p) if ($p[3] === $sup && count($rows) < 4) { $q = mt_rand(20, 80); $rows[] = [$i + 1, $q, $p[4]]; $tot += $q * $p[4]; }
        $poSt->execute([$sup, 2, $od, 'Received', $tot, (new DateTime($od))->modify('+2 days')->format('Y-m-d')]);
        $po = $pdo->lastInsertId(); foreach ($rows as $r) $poi->execute([$po, $r[0], $r[1], $r[2]]);
    }
    $pdo->commit();
    return 'Database "' . $c['name'] . '" installed with demo data.';
}
