<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MasterData\Item;
use App\Models\MasterData\ItemCategory;
use App\Models\MasterData\Unit;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Location;
use App\Models\MasterData\WorkCenter;
use App\Models\MasterData\ProductionLine;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderLine;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\PurchaseOrderLine;
use App\Models\Production\ProductionOrder;
use App\Models\Production\BomHeader;
use App\Enums\QualityInspectionStatus;
use App\Models\Production\BomLine;
use App\Models\Production\RoutingHeader;
use App\Models\Production\RoutingOperation;
use App\Models\Quality\QualityInspection;
use App\Models\Quality\QualityInspectionLine;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryTransaction;
use App\Models\System\NumberSequence;
use App\Enums\SalesOrderStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\ProductionOrderStatus;
use App\Enums\InspectionType;
use App\Enums\QualityResult;
use App\Enums\InventoryTransactionType;
use App\Enums\InventoryQualityStatus;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    // 用于存储创建过程中产生的ID引用
    private array $users = [];
    private array $categories = [];
    private array $units = [];
    private array $items = [];
    private array $customers = [];
    private array $suppliers = [];
    private array $warehouses = [];
    private array $locations = [];
    private array $workCenters = [];
    private array $productionLines = [];
    private array $boms = [];
    private array $routings = [];

    public function run(): void
    {
        // ========================================
        // 0. 确认角色已存在
        // ========================================
        $this->ensureRolesExist();

        // ========================================
        // 1. 创建演示用户（每个角色一人，密码 123456）
        // ========================================
        $this->createDemoUsers();

        // ========================================
        // 2. 基础数据
        // ========================================
        $this->createItemCategories();
        $this->createUnits();
        $this->createItems();
        $this->createCustomers();
        $this->createSuppliers();
        $this->createWarehouses();
        $this->createLocations();
        $this->createWorkCentersAndProductionLines();

        // ========================================
        // 3. 编码序列
        // ========================================
        $this->createNumberSequences();

        // ========================================
        // 4. BOM & 工艺路线
        // ========================================
        $this->createBoms();
        $this->createRoutings();

        // ========================================
        // 5. 业务数据
        // ========================================
        $this->createSalesOrders();
        $this->createPurchaseOrders();
        $this->createProductionOrders();
        $this->createQualityInspections();
        $this->createInventoryData();

        $this->command?->info('演示数据创建完成！');
        $this->command?->info('共创建: ' . count($this->users) . ' 个用户, '
            . count($this->items) . ' 个物料, '
            . count($this->customers) . ' 个客户, '
            . count($this->suppliers) . ' 个供应商');
    }

    // ============================================
    // 0. 确认角色存在
    // ============================================
    private function ensureRolesExist(): void
    {
        $roles = [
            'admin', 'sales_manager', 'purchase_manager',
            'production_manager', 'warehouse_manager', 'quality_manager',
            'accountant', 'viewer',
            'hr_specialist', 'project_manager', 'asset_manager', 'ecommerce_operator',
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    // ============================================
    // 1. 创建演示用户
    // ============================================
    private function createDemoUsers(): void
    {
        $password = '123456'; // 明文，让 User 模型 hashed cast 自动哈希

        $demoUsers = [
            ['name' => 'admin',     'email' => 'admin@admin.com',     'role' => 'admin', 'is_admin' => true],
            ['name' => '张销售',       'email' => 'sales@demo.com',      'role' => 'sales_manager'],
            ['name' => '李采购',       'email' => 'purchase@demo.com',   'role' => 'purchase_manager'],
            ['name' => '王生产',       'email' => 'production@demo.com', 'role' => 'production_manager'],
            ['name' => '赵仓储',       'email' => 'warehouse@demo.com',  'role' => 'warehouse_manager'],
            ['name' => '孙质检',       'email' => 'quality@demo.com',    'role' => 'quality_manager'],
            ['name' => '周财务',       'email' => 'accounting@demo.com', 'role' => 'accountant'],
            ['name' => '吴查看',       'email' => 'viewer@demo.com',     'role' => 'viewer'],
            ['name' => '郑人事',       'email' => 'hr@demo.com',         'role' => 'hr_specialist'],
            ['name' => '褚项目',       'email' => 'project@demo.com',    'role' => 'project_manager'],
            ['name' => '卫资产',       'email' => 'asset@demo.com',      'role' => 'asset_manager'],
            ['name' => '蒋电商',       'email' => 'ec@demo.com',         'role' => 'ecommerce_operator'],
        ];

        foreach ($demoUsers as $i => $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => $password,
                    'is_admin' => $u['is_admin'] ?? false,
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([$u['role']]);
            $this->users[$u['role']] = $user;
        }
    }

    // ============================================
    // 2a. 物料分类
    // ============================================
    private function createItemCategories(): void
    {
        $cats = [
            ['code' => 'RM', 'name' => '原材料',     'description' => '生产用原材料'],
            ['code' => 'SF', 'name' => '半成品',     'description' => '加工过程中的半成品'],
            ['code' => 'FG', 'name' => '成品',       'description' => '最终产成品'],
            ['code' => 'PK', 'name' => '包装材料',   'description' => '产品包装用材料'],
            ['code' => 'SP', 'name' => '备品备件',   'description' => '设备维修备件'],
        ];
        foreach ($cats as $c) {
            $this->categories[$c['code']] = ItemCategory::updateOrCreate(
                ['code' => $c['code']],
                ['name' => $c['name'], 'description' => $c['description'], 'active' => true]
            );
        }
    }

    // ============================================
    // 2b. 计量单位
    // ============================================
    private function createUnits(): void
    {
        $units = [
            ['code' => 'PCS', 'name' => '个',   'short_name' => '个'],
            ['code' => 'BOX', 'name' => '箱',   'short_name' => '箱'],
            ['code' => 'KG',  'name' => '千克', 'short_name' => 'kg'],
            ['code' => 'M',   'name' => '米',   'short_name' => 'm'],
            ['code' => 'L',   'name' => '升',   'short_name' => 'L'],
            ['code' => 'SET', 'name' => '套',   'short_name' => '套'],
        ];
        foreach ($units as $u) {
            $this->units[$u['code']] = Unit::updateOrCreate(
                ['code' => $u['code']],
                ['name' => $u['name'], 'short_name' => $u['short_name'], 'active' => true]
            );
        }
    }

    // ============================================
    // 2c. 物料
    // ============================================
    private function createItems(): void
    {
        $items = [
            // 原材料
            ['code'=>'M-001','name'=>'冷轧钢板 SPCC 1.0mm','spec'=>'1.0mm×1250mm×2500mm','category'=>'RM','unit'=>'KG','item_type'=>'purchased','standard_cost'=>6.50,'safety_stock'=>500],
            ['code'=>'M-002','name'=>'不锈钢板 SUS304 2.0mm','spec'=>'2.0mm×1500mm×3000mm','category'=>'RM','unit'=>'KG','item_type'=>'purchased','standard_cost'=>25.00,'safety_stock'=>300],
            ['code'=>'M-003','name'=>'铝合金型材 6063-T5','spec'=>'40×40×2mm','category'=>'RM','unit'=>'M','item_type'=>'purchased','standard_cost'=>35.00,'safety_stock'=>200],
            ['code'=>'M-004','name'=>'电子控制板 PCB-V2','spec'=>'V2.0 双层板 100×60mm','category'=>'RM','unit'=>'PCS','item_type'=>'purchased','standard_cost'=>85.00,'safety_stock'=>100],
            ['code'=>'M-005','name'=>'直流电机 24V 100W','spec'=>'24VDC 100W 3000RPM','category'=>'RM','unit'=>'PCS','item_type'=>'purchased','standard_cost'=>320.00,'safety_stock'=>50],
            // 半成品
            ['code'=>'S-001','name'=>'焊接框架组件 A型','spec'=>'1200×800×100mm','category'=>'SF','unit'=>'PCS','item_type'=>'manufactured','standard_cost'=>180.00,'safety_stock'=>30],
            ['code'=>'S-002','name'=>'电控总成组件','spec'=>'含PCB+电机+线束','category'=>'SF','unit'=>'SET','item_type'=>'manufactured','standard_cost'=>550.00,'safety_stock'=>20],
            // 成品
            ['code'=>'F-001','name'=>'智能传送带单元 CT-200','spec'=>'2000×800×1200mm 220V 2kW','category'=>'FG','unit'=>'SET','item_type'=>'manufactured','standard_cost'=>2800.00,'safety_stock'=>10],
            ['code'=>'F-002','name'=>'自动化分拣模块 AS-100','spec'=>'1500×600×1800mm 220V 1.5kW','category'=>'FG','unit'=>'SET','item_type'=>'manufactured','standard_cost'=>4500.00,'safety_stock'=>5],
            // 包装材料
            ['code'=>'P-001','name'=>'瓦楞纸箱 大号','spec'=>'1200×800×600mm 五层','category'=>'PK','unit'=>'PCS','item_type'=>'purchased','standard_cost'=>12.00,'safety_stock'=>200],
            ['code'=>'P-002','name'=>'EPE珍珠棉内衬','spec'=>'定制型 10mm厚','category'=>'PK','unit'=>'SET','item_type'=>'purchased','standard_cost'=>8.00,'safety_stock'=>150],
        ];

        $adminId = $this->users['admin']->id ?? 1;
        foreach ($items as $it) {
            $this->items[$it['code']] = Item::updateOrCreate(
                ['code' => $it['code']],
                [
                    'name' => $it['name'],
                    'spec' => $it['spec'] ?? null,
                    'category_id' => $this->categories[$it['category']]->id ?? null,
                    'unit_id' => $this->units[$it['unit']]->id ?? null,
                    'item_type' => $it['item_type'],
                    'standard_cost' => $it['standard_cost'],
                    'safety_stock' => $it['safety_stock'],
                    'active' => true,
                    'created_by' => $adminId,
                ]
            );
        }
    }

    // ============================================
    // 2d. 客户
    // ============================================
    private function createCustomers(): void
    {
        $customers = [
            ['code'=>'C-001','name'=>'华东制造集团有限公司','contact_person'=>'陈总','phone'=>'13800001001','email'=>'chen@eastchina.com','address'=>'上海市浦东新区张江路100号','tax_id'=>'91310000MA1K123456'],
            ['code'=>'C-002','name'=>'华南自动化科技有限公司','contact_person'=>'林经理','phone'=>'13800001002','email'=>'lin@southchina.com','address'=>'深圳市南山区科技园南路200号','tax_id'=>'91440300MA2K123456'],
            ['code'=>'C-003','name'=>'北京精工设备有限公司','contact_person'=>'刘工','phone'=>'13800001003','email'=>'liu@jinggong.com','address'=>'北京市大兴区亦庄经济开发区300号','tax_id'=>'91110108MA3K123456'],
            ['code'=>'C-004','name'=>'西南物流装备有限公司','contact_person'=>'黄总','phone'=>'13800001004','email'=>'huang@xnlog.com','address'=>'成都市高新区天府大道400号','tax_id'=>'91510100MA4K123456'],
            ['code'=>'C-005','name'=>'中亚机械制造有限公司','contact_person'=>'杨经理','phone'=>'13800001005','email'=>'yang@zyjx.com','address'=>'武汉市东湖新技术开发区500号','tax_id'=>'91420100MA5K123456'],
        ];
        $adminId = $this->users['admin']->id ?? 1;
        foreach ($customers as $c) {
            $this->customers[$c['code']] = Customer::updateOrCreate(
                ['code' => $c['code']],
                [
                    'name' => $c['name'],
                    'contact_person' => $c['contact_person'],
                    'phone' => $c['phone'],
                    'email' => $c['email'],
                    'address' => $c['address'],
                    'tax_id' => $c['tax_id'],
                    'active' => true,
                    'created_by' => $adminId,
                ]
            );
        }
    }

    // ============================================
    // 2e. 供应商
    // ============================================
    private function createSuppliers(): void
    {
        $suppliers = [
            ['code'=>'S-001','name'=>'宝钢钢材贸易有限公司','contact_person'=>'赵经理','phone'=>'13900002001','email'=>'zhao@baosteel.com','address'=>'上海市宝山区宝钢路1号','tax_id'=>'91310000MA1B654321'],
            ['code'=>'S-002','name'=>'深圳华强电子有限公司','contact_person'=>'王总','phone'=>'13900002002','email'=>'wang@huaqiang.com','address'=>'深圳市龙华区清湖工业园2号','tax_id'=>'91440300MA2B654321'],
            ['code'=>'S-003','name'=>'江苏精密电机有限公司','contact_person'=>'钱工','phone'=>'13900002003','email'=>'qian@jmotor.com','address'=>'苏州市工业园区苏虹路3号','tax_id'=>'91320500MA3B654321'],
            ['code'=>'S-004','name'=>'浙江铝合金型材厂','contact_person'=>'孙经理','phone'=>'13900002004','email'=>'sun@zjal.com','address'=>'杭州市余杭区未来科技城4号','tax_id'=>'91330100MA4B654321'],
            ['code'=>'S-005','name'=>'广东包装制品有限公司','contact_person'=>'周总','phone'=>'13900002005','email'=>'zhou@gdpack.com','address'=>'东莞市长安镇振安路5号','tax_id'=>'91441900MA5B654321'],
        ];
        $adminId = $this->users['admin']->id ?? 1;
        foreach ($suppliers as $s) {
            $this->suppliers[$s['code']] = Supplier::updateOrCreate(
                ['code' => $s['code']],
                [
                    'name' => $s['name'],
                    'contact_person' => $s['contact_person'],
                    'phone' => $s['phone'],
                    'email' => $s['email'],
                    'address' => $s['address'],
                    'tax_id' => $s['tax_id'],
                    'active' => true,
                    'created_by' => $adminId,
                ]
            );
        }
    }

    // ============================================
    // 2f. 仓库
    // ============================================
    private function createWarehouses(): void
    {
        $warehouses = [
            ['code'=>'WH-RAW','name'=>'原材料仓库','warehouse_type'=>'raw','address'=>'A栋1楼','active'=>true],
            ['code'=>'WH-SF', 'name'=>'半成品仓库','warehouse_type'=>'semi','address'=>'B栋1楼','active'=>true],
            ['code'=>'WH-FG', 'name'=>'成品仓库',  'warehouse_type'=>'finished','address'=>'C栋1楼','active'=>true],
            ['code'=>'WH-QC', 'name'=>'待检隔离仓','warehouse_type'=>'qc','address'=>'质检区旁','active'=>true],
        ];
        $adminId = $this->users['admin']->id ?? 1;
        foreach ($warehouses as $w) {
            $this->warehouses[$w['code']] = Warehouse::updateOrCreate(
                ['code' => $w['code']],
                [
                    'name' => $w['name'],
                    'description' => $w['name'],
                    'address' => $w['address'],
                    'warehouse_type' => $w['warehouse_type'],
                    'active' => $w['active'],
                    'created_by' => $adminId,
                ]
            );
        }
    }

    // ============================================
    // 2g. 库位
    // ============================================
    private function createLocations(): void
    {
        $locs = [
            ['code'=>'A-01-01','name'=>'A区-01排-01位','warehouse_code'=>'WH-RAW'],
            ['code'=>'A-01-02','name'=>'A区-01排-02位','warehouse_code'=>'WH-RAW'],
            ['code'=>'A-02-01','name'=>'A区-02排-01位','warehouse_code'=>'WH-RAW'],
            ['code'=>'B-01-01','name'=>'B区-01排-01位','warehouse_code'=>'WH-SF'],
            ['code'=>'C-01-01','name'=>'C区-01排-01位','warehouse_code'=>'WH-FG'],
            ['code'=>'C-01-02','name'=>'C区-01排-02位','warehouse_code'=>'WH-FG'],
        ];
        $adminId = $this->users['admin']->id ?? 1;
        foreach ($locs as $l) {
            $wh = $this->warehouses[$l['warehouse_code']] ?? null;
            $this->locations[$l['code']] = Location::updateOrCreate(
                ['code' => $l['code']],
                [
                    'name' => $l['name'],
                    'warehouse_id' => $wh ? $wh->id : null,
                    'active' => true,
                    'created_by' => $adminId,
                ]
            );
        }
    }

    // ============================================
    // 2h. 工作中心 & 产线
    // ============================================
    private function createWorkCentersAndProductionLines(): void
    {
        $wcs = [
            ['code'=>'WC-CUT', 'name'=>'下料车间',   'description'=>'钢板切割、型材下料'],
            ['code'=>'WC-WELD','name'=>'焊接车间',   'description'=>'框架焊接、钣金焊接'],
            ['code'=>'WC-ASM', 'name'=>'总装车间',   'description'=>'部件组装、成品组装'],
            ['code'=>'WC-TST', 'name'=>'测试车间',   'description'=>'电气测试、运行测试'],
            ['code'=>'WC-PKG', 'name'=>'包装车间',   'description'=>'成品包装、打包发货'],
        ];
        $adminId = $this->users['admin']->id ?? 1;
        foreach ($wcs as $wc) {
            $this->workCenters[$wc['code']] = WorkCenter::updateOrCreate(
                ['code' => $wc['code']],
                ['name' => $wc['name'], 'description' => $wc['description'], 'active' => true, 'created_by' => $adminId]
            );
        }

        $pls = [
            ['code'=>'PL-01','name'=>'传送带装配线 1号线','work_center_code'=>'WC-ASM'],
            ['code'=>'PL-02','name'=>'分拣模块装配线 2号线','work_center_code'=>'WC-ASM'],
            ['code'=>'PL-03','name'=>'焊接生产线 3号线', 'work_center_code'=>'WC-WELD'],
        ];
        foreach ($pls as $pl) {
            $wc = $this->workCenters[$pl['work_center_code']] ?? null;
            $this->productionLines[$pl['code']] = ProductionLine::updateOrCreate(
                ['code' => $pl['code']],
                [
                    'name' => $pl['name'],
                    'work_center_id' => $wc ? $wc->id : null,
                    'active' => true,
                    'created_by' => $adminId,
                ]
            );
        }
    }

    // ============================================
    // 3. 编码序列
    // ============================================
    private function createNumberSequences(): void
    {
        $seeds = [
            ['code'=>'SO','name'=>'销售订单','prefix'=>'SO','current_number'=>2000,'year_format'=>'Y'],
            ['code'=>'PO','name'=>'采购订单','prefix'=>'PO','current_number'=>2000,'year_format'=>'Y'],
            ['code'=>'MO','name'=>'生产工单','prefix'=>'MO','current_number'=>2000,'year_format'=>'Y'],
            ['code'=>'QI','name'=>'质量检验','prefix'=>'QI','current_number'=>2000,'year_format'=>'Y'],
            ['code'=>'TR','name'=>'库存交易','prefix'=>'TR','current_number'=>2000,'year_format'=>'Y'],
        ];
        $adminId = $this->users['admin']->id ?? 1;
        foreach ($seeds as $s) {
            NumberSequence::updateOrCreate(
                ['code' => $s['code']],
                [
                    'name' => $s['name'],
                    'prefix' => $s['prefix'],
                    'number_length' => 6,
                    'current_number' => $s['current_number'],
                    'year_format' => $s['year_format'],
                    'year_reset' => true,
                    'month_reset' => false,
                    'active' => true,
                    'created_by' => $adminId,
                ]
            );
        }
    }

    // ============================================
    // 4a. BOM
    // ============================================
    private function createBoms(): void
    {
        $adminId = $this->users['admin']->id ?? 1;
        $prodId = $this->users['production_manager']->id ?? $adminId;

        // BOM 1: 智能传送带单元 CT-200
        $bom1 = BomHeader::updateOrCreate(
            ['code' => 'BOM-CT200-V2'],
            [
                'item_id' => $this->items['F-001']->id,
                'version' => '2.0',
                'quantity' => 1,
                'effective_date' => '2026-01-01',
                'status' => 'approved',
                'description' => '智能传送带单元 CT-200 BOM',
                'created_by' => $adminId,
                'approved_by' => $prodId,
                'approved_at' => now(),
            ]
        );
        $bomLines = [
            ['line'=>10, 'item'=>'S-001', 'qty'=>1.00, 'scrap'=>0.02],
            ['line'=>20, 'item'=>'S-002', 'qty'=>1.00, 'scrap'=>0.01],
            ['line'=>30, 'item'=>'P-001', 'qty'=>1.00, 'scrap'=>0.00],
            ['line'=>40, 'item'=>'P-002', 'qty'=>1.00, 'scrap'=>0.00],
        ];
        foreach ($bomLines as $bl) {
            $item = $this->items[$bl['item']];
            BomLine::updateOrCreate(
                ['bom_header_id' => $bom1->id, 'line_number' => $bl['line']],
                [
                    'item_id' => $item->id,
                    'quantity' => $bl['qty'],
                    'scrap_rate' => $bl['scrap'],
                    'net_quantity' => $bl['qty'] * (1 + $bl['scrap']),
                    'unit_id' => $item->unit_id,
                ]
            );
        }
        $this->boms['BOM-CT200-V2'] = $bom1;

        // BOM 2: 自动化分拣模块 AS-100
        $bom2 = BomHeader::updateOrCreate(
            ['code' => 'BOM-AS100-V1'],
            [
                'item_id' => $this->items['F-002']->id,
                'version' => '1.0',
                'quantity' => 1,
                'effective_date' => '2026-03-01',
                'status' => 'approved',
                'description' => '自动化分拣模块 AS-100 BOM',
                'created_by' => $adminId,
                'approved_by' => $prodId,
                'approved_at' => now(),
            ]
        );
        $bomLines2 = [
            ['line'=>10, 'item'=>'S-001', 'qty'=>1.00, 'scrap'=>0.02],
            ['line'=>20, 'item'=>'S-002', 'qty'=>2.00, 'scrap'=>0.01],
            ['line'=>30, 'item'=>'M-003', 'qty'=>3.00, 'scrap'=>0.03],
            ['line'=>40, 'item'=>'P-001', 'qty'=>1.00, 'scrap'=>0.00],
            ['line'=>50, 'item'=>'P-002', 'qty'=>2.00, 'scrap'=>0.00],
        ];
        foreach ($bomLines2 as $bl) {
            $item = $this->items[$bl['item']];
            BomLine::updateOrCreate(
                ['bom_header_id' => $bom2->id, 'line_number' => $bl['line']],
                [
                    'item_id' => $item->id,
                    'quantity' => $bl['qty'],
                    'scrap_rate' => $bl['scrap'],
                    'net_quantity' => $bl['qty'] * (1 + $bl['scrap']),
                    'unit_id' => $item->unit_id,
                ]
            );
        }
        $this->boms['BOM-AS100-V1'] = $bom2;
    }

    // ============================================
    // 4b. 工艺路线
    // ============================================
    private function createRoutings(): void
    {
        $adminId = $this->users['admin']->id ?? 1;
        $prodId = $this->users['production_manager']->id ?? $adminId;

        // Routing 1: CT-200
        $routing1 = RoutingHeader::updateOrCreate(
            ['code' => 'ROUTE-CT200'],
            [
                'item_id' => $this->items['F-001']->id,
                'version' => '2.0',
                'effective_date' => '2026-01-01',
                'status' => 'approved',
                'description' => '智能传送带单元 CT-200 工艺路线',
                'created_by' => $adminId,
                'approved_by' => $prodId,
                'approved_at' => now(),
            ]
        );
        $ops1 = [
            ['num'=>10, 'code'=>'OP-10', 'wc'=>'WC-CUT',  'name'=>'下料切割',     'time'=>2.5, 'setup'=>0.5, 'report'=>true, 'insp'=>true],
            ['num'=>20, 'code'=>'OP-20', 'wc'=>'WC-WELD', 'name'=>'框架焊接',     'time'=>4.0, 'setup'=>1.0, 'report'=>true, 'insp'=>true],
            ['num'=>30, 'code'=>'OP-30', 'wc'=>'WC-ASM',  'name'=>'电气组装',     'time'=>6.0, 'setup'=>1.5, 'report'=>true, 'insp'=>true],
            ['num'=>40, 'code'=>'OP-40', 'wc'=>'WC-TST',  'name'=>'运行测试',     'time'=>3.0, 'setup'=>0.5, 'report'=>true, 'insp'=>true],
            ['num'=>50, 'code'=>'OP-50', 'wc'=>'WC-PKG',  'name'=>'包装出货',     'time'=>1.5, 'setup'=>0.3, 'report'=>true, 'insp'=>false],
        ];
        foreach ($ops1 as $op) {
            RoutingOperation::updateOrCreate(
                ['routing_header_id' => $routing1->id, 'operation_number' => $op['num']],
                [
                    'operation_code' => $op['code'],
                    'operation_name' => $op['name'],
                    'work_center_id' => $this->workCenters[$op['wc']]->id,
                    'standard_time' => $op['time'],
                    'setup_time' => $op['setup'],
                    'require_report' => $op['report'],
                    'require_inspection' => $op['insp'],
                    'is_subcontract' => false,
                ]
            );
        }
        $this->routings['ROUTE-CT200'] = $routing1;

        // Routing 2: AS-100
        $routing2 = RoutingHeader::updateOrCreate(
            ['code' => 'ROUTE-AS100'],
            [
                'item_id' => $this->items['F-002']->id,
                'version' => '1.0',
                'effective_date' => '2026-03-01',
                'status' => 'approved',
                'description' => '自动化分拣模块 AS-100 工艺路线',
                'created_by' => $adminId,
                'approved_by' => $prodId,
                'approved_at' => now(),
            ]
        );
        $ops2 = [
            ['num'=>10, 'code'=>'OP-10', 'wc'=>'WC-CUT',  'name'=>'型材下料',     'time'=>3.0, 'setup'=>0.5, 'report'=>true, 'insp'=>true],
            ['num'=>20, 'code'=>'OP-20', 'wc'=>'WC-WELD', 'name'=>'主体焊接',     'time'=>5.0, 'setup'=>1.0, 'report'=>true, 'insp'=>true],
            ['num'=>30, 'code'=>'OP-30', 'wc'=>'WC-ASM',  'name'=>'整机装配',     'time'=>8.0, 'setup'=>2.0, 'report'=>true, 'insp'=>true],
            ['num'=>40, 'code'=>'OP-40', 'wc'=>'WC-TST',  'name'=>'性能测试',     'time'=>4.0, 'setup'=>1.0, 'report'=>true, 'insp'=>true],
            ['num'=>50, 'code'=>'OP-50', 'wc'=>'WC-PKG',  'name'=>'包装发货',     'time'=>2.0, 'setup'=>0.5, 'report'=>true, 'insp'=>false],
        ];
        foreach ($ops2 as $op) {
            RoutingOperation::updateOrCreate(
                ['routing_header_id' => $routing2->id, 'operation_number' => $op['num']],
                [
                    'operation_code' => $op['code'],
                    'operation_name' => $op['name'],
                    'work_center_id' => $this->workCenters[$op['wc']]->id,
                    'standard_time' => $op['time'],
                    'setup_time' => $op['setup'],
                    'require_report' => $op['report'],
                    'require_inspection' => $op['insp'],
                    'is_subcontract' => false,
                ]
            );
        }
        $this->routings['ROUTE-AS100'] = $routing2;
    }

    // ============================================
    // 5. 销售订单（覆盖多种状态）
    // ============================================
    private function createSalesOrders(): void
    {
        $salesId = $this->users['sales_manager']->id ?? 2;
        $adminId = $this->users['admin']->id ?? 1;
        $customer = $this->customers['C-001'];
        $year = date('Y');

        $soData = [
            // 1. 草稿
            ['order_no'=>"SO-{$year}-002001", 'customer'=>'C-001', 'date'=>'2026-07-10', 'delivery'=>'2026-07-30', 'status'=>'draft', 'created_by'=>$salesId],
            // 2. 已提交（待审核）
            ['order_no'=>"SO-{$year}-002002", 'customer'=>'C-002', 'date'=>'2026-07-08', 'delivery'=>'2026-07-25', 'status'=>'submitted', 'created_by'=>$salesId],
            // 3. 已审核（可以发货）
            ['order_no'=>"SO-{$year}-002003", 'customer'=>'C-003', 'date'=>'2026-07-05', 'delivery'=>'2026-07-20', 'status'=>'approved', 'created_by'=>$salesId, 'approved_by'=>$adminId, 'approved_at'=>now()],
            // 4. 部分发货
            ['order_no'=>"SO-{$year}-002004", 'customer'=>'C-001', 'date'=>'2026-07-01', 'delivery'=>'2026-07-15', 'status'=>'partially_delivered', 'created_by'=>$salesId, 'approved_by'=>$adminId, 'approved_at'=>now()],
            // 5. 已完成（全部发货）
            ['order_no'=>"SO-{$year}-002005", 'customer'=>'C-004', 'date'=>'2026-06-20', 'delivery'=>'2026-07-05', 'status'=>'fully_delivered', 'created_by'=>$salesId, 'approved_by'=>$adminId, 'approved_at'=>now()],
            // 6. 已关闭
            ['order_no'=>"SO-{$year}-002006", 'customer'=>'C-005', 'date'=>'2026-06-01', 'delivery'=>'2026-06-20', 'status'=>'closed', 'created_by'=>$salesId, 'approved_by'=>$adminId, 'approved_at'=>now()],
            // 7. 已取消
            ['order_no'=>"SO-{$year}-002007", 'customer'=>'C-002', 'date'=>'2026-07-12', 'delivery'=>'2026-08-01', 'status'=>'cancelled', 'created_by'=>$salesId],
        ];

        $lineTemplates = [
            ['item'=>'F-001', 'qty'=>5, 'price'=>3500.00],
            ['item'=>'F-002', 'qty'=>3, 'price'=>5500.00],
        ];

        foreach ($soData as $i => $so) {
            $order = SalesOrder::updateOrCreate(
                ['order_no' => $so['order_no']],
                [
                    'customer_id' => $this->customers[$so['customer']]->id,
                    'order_date' => $so['date'],
                    'delivery_date' => $so['delivery'],
                    'status' => $so['status'],
                    'total_amount' => 0,
                    'total_quantity' => 0,
                    'created_by' => $so['created_by'],
                    'approved_by' => $so['approved_by'] ?? null,
                    'approved_at' => $so['approved_at'] ?? null,
                ]
            );

            // 确保有行项目
            if ($order->lines()->count() == 0) {
                $totalAmount = 0;
                $totalQty = 0;
                $templates = ($i % 2 == 0)
                    ? [$lineTemplates[0], ['item'=>'P-001', 'qty'=>5*($i+1), 'price'=>12.00]]
                    : [$lineTemplates[1], $lineTemplates[0], ['item'=>'P-002', 'qty'=>3*($i+1), 'price'=>8.00]];

                foreach ($templates as $ln => $lt) {
                    $item = $this->items[$lt['item']];
                    $amount = $lt['qty'] * $lt['price'];
                    SalesOrderLine::create([
                        'sales_order_id' => $order->id,
                        'line_number' => ($ln + 1) * 10,
                        'item_id' => $item->id,
                        'quantity' => $lt['qty'],
                        'unit_price' => $lt['price'],
                        'line_amount' => $amount,
                        'warehouse_id' => $this->warehouses['WH-FG']->id,
                    ]);
                    $totalAmount += $amount;
                    $totalQty += $lt['qty'];
                }
                $order->update(['total_amount' => $totalAmount, 'total_quantity' => $totalQty]);
            }
        }
    }

    // ============================================
    // 6. 采购订单（覆盖多种状态）
    // ============================================
    private function createPurchaseOrders(): void
    {
        $purchaseId = $this->users['purchase_manager']->id ?? 3;
        $adminId = $this->users['admin']->id ?? 1;
        $year = date('Y');

        $poData = [
            // 1. 草稿
            ['order_no'=>"PO-{$year}-002001", 'supplier'=>'S-001', 'date'=>'2026-07-11', 'delivery'=>'2026-07-25', 'status'=>'draft', 'created_by'=>$purchaseId],
            // 2. 已提交
            ['order_no'=>"PO-{$year}-002002", 'supplier'=>'S-002', 'date'=>'2026-07-09', 'delivery'=>'2026-07-22', 'status'=>'submitted', 'created_by'=>$purchaseId],
            // 3. 已审核
            ['order_no'=>"PO-{$year}-002003", 'supplier'=>'S-003', 'date'=>'2026-07-05', 'delivery'=>'2026-07-18', 'status'=>'approved', 'created_by'=>$purchaseId, 'approved_by'=>$adminId, 'approved_at'=>now()],
            // 4. 部分收货
            ['order_no'=>"PO-{$year}-002004", 'supplier'=>'S-001', 'date'=>'2026-07-01', 'delivery'=>'2026-07-15', 'status'=>'partially_received', 'created_by'=>$purchaseId, 'approved_by'=>$adminId, 'approved_at'=>now()],
            // 5. 全部收货
            ['order_no'=>"PO-{$year}-002005", 'supplier'=>'S-004', 'date'=>'2026-06-25', 'delivery'=>'2026-07-10', 'status'=>'fully_received', 'created_by'=>$purchaseId, 'approved_by'=>$adminId, 'approved_at'=>now()],
            // 6. 已关闭
            ['order_no'=>"PO-{$year}-002006", 'supplier'=>'S-005', 'date'=>'2026-06-10', 'delivery'=>'2026-06-25', 'status'=>'closed', 'created_by'=>$purchaseId, 'approved_by'=>$adminId, 'approved_at'=>now()],
            // 7. 已取消
            ['order_no'=>"PO-{$year}-002007", 'supplier'=>'S-002', 'date'=>'2026-07-13', 'delivery'=>'2026-07-28', 'status'=>'cancelled', 'created_by'=>$purchaseId],
        ];

        $poLineTemplates = [
            ['item'=>'M-001', 'qty'=>200, 'price'=>6.50],
            ['item'=>'M-002', 'qty'=>100, 'price'=>25.00],
            ['item'=>'M-005', 'qty'=>30,  'price'=>320.00],
        ];

        foreach ($poData as $i => $po) {
            $order = PurchaseOrder::updateOrCreate(
                ['order_no' => $po['order_no']],
                [
                    'supplier_id' => $this->suppliers[$po['supplier']]->id,
                    'order_date' => $po['date'],
                    'delivery_date' => $po['delivery'],
                    'status' => $po['status'],
                    'total_amount' => 0,
                    'total_quantity' => 0,
                    'created_by' => $po['created_by'],
                    'approved_by' => $po['approved_by'] ?? null,
                    'approved_at' => $po['approved_at'] ?? null,
                ]
            );

            if ($order->lines()->count() == 0) {
                $totalAmount = 0;
                $totalQty = 0;
                $selected = $i < 2 ? array_slice($poLineTemplates, 0, 2) : $poLineTemplates;
                foreach ($selected as $ln => $lt) {
                    $item = $this->items[$lt['item']];
                    $amount = $lt['qty'] * $lt['price'];
                    PurchaseOrderLine::create([
                        'purchase_order_id' => $order->id,
                        'line_number' => ($ln + 1) * 10,
                        'item_id' => $item->id,
                        'quantity' => $lt['qty'],
                        'unit_price' => $lt['price'],
                        'line_amount' => $amount,
                        'warehouse_id' => $this->warehouses['WH-RAW']->id,
                    ]);
                    $totalAmount += $amount;
                    $totalQty += $lt['qty'];
                }
                $order->update(['total_amount' => $totalAmount, 'total_quantity' => $totalQty]);
            }
        }
    }

    // ============================================
    // 7. 生产工单（覆盖多种状态）
    // ============================================
    private function createProductionOrders(): void
    {
        $prodId = $this->users['production_manager']->id ?? 4;
        $adminId = $this->users['admin']->id ?? 1;
        $year = date('Y');

        $bom1 = $this->boms['BOM-CT200-V2'] ?? null;
        $routing1 = $this->routings['ROUTE-CT200'] ?? null;
        $bom2 = $this->boms['BOM-AS100-V1'] ?? null;
        $routing2 = $this->routings['ROUTE-AS100'] ?? null;

        $moData = [
            // 1. 草稿
            ['order_no'=>"MO-{$year}-002001", 'item'=>'F-001', 'qty'=>10, 'start'=>'2026-07-10', 'end'=>'2026-07-25', 'status'=>'draft', 'bom'=>'BOM-CT200-V2', 'route'=>'ROUTE-CT200', 'created_by'=>$prodId],
            // 2. 已下达
            ['order_no'=>"MO-{$year}-002002", 'item'=>'F-002', 'qty'=>5,  'start'=>'2026-07-08', 'end'=>'2026-07-22', 'status'=>'released', 'bom'=>'BOM-AS100-V1', 'route'=>'ROUTE-AS100', 'created_by'=>$prodId, 'approved_by'=>$adminId, 'approved_at'=>now()],
            // 3. 生产中
            ['order_no'=>"MO-{$year}-002003", 'item'=>'F-001', 'qty'=>8,  'start'=>'2026-07-05', 'end'=>'2026-07-20', 'status'=>'in_progress', 'bom'=>'BOM-CT200-V2', 'route'=>'ROUTE-CT200', 'created_by'=>$prodId, 'approved_by'=>$adminId, 'approved_at'=>now(), 'completed_qty'=>3],
            // 4. 已完成
            ['order_no'=>"MO-{$year}-002004", 'item'=>'F-001', 'qty'=>15, 'start'=>'2026-07-01', 'end'=>'2026-07-15', 'status'=>'completed', 'bom'=>'BOM-CT200-V2', 'route'=>'ROUTE-CT200', 'created_by'=>$prodId, 'approved_by'=>$adminId, 'approved_at'=>now(), 'completed_qty'=>15],
            // 5. 已关闭
            ['order_no'=>"MO-{$year}-002005", 'item'=>'F-002', 'qty'=>12, 'start'=>'2026-06-20', 'end'=>'2026-07-05', 'status'=>'closed', 'bom'=>'BOM-AS100-V1', 'route'=>'ROUTE-AS100', 'created_by'=>$prodId, 'approved_by'=>$adminId, 'approved_at'=>now(), 'completed_qty'=>12],
            // 6. 已取消
            ['order_no'=>"MO-{$year}-002006", 'item'=>'F-002', 'qty'=>4,  'start'=>'2026-07-12', 'end'=>'2026-07-28', 'status'=>'cancelled', 'bom'=>'BOM-AS100-V1', 'route'=>'ROUTE-AS100', 'created_by'=>$prodId],
        ];

        foreach ($moData as $mo) {
            $bom = $this->boms[$mo['bom']] ?? null;
            $routing = $this->routings[$mo['route']] ?? null;
            $item = $this->items[$mo['item']];

            ProductionOrder::updateOrCreate(
                ['order_no' => $mo['order_no']],
                [
                    'item_id' => $item->id,
                    'quantity' => $mo['qty'],
                    'completed_quantity' => $mo['completed_qty'] ?? 0,
                    'scrap_quantity' => 0,
                    'status' => $mo['status'],
                    'bom_header_id' => $bom ? $bom->id : null,
                    'bom_version' => $bom ? $bom->version : null,
                    'routing_header_id' => $routing ? $routing->id : null,
                    'routing_version' => $routing ? $routing->version : null,
                    'plan_start_date' => $mo['start'],
                    'plan_end_date' => $mo['end'],
                    'actual_start_date' => in_array($mo['status'], ['in_progress','completed','closed']) ? $mo['start'] : null,
                    'actual_end_date' => in_array($mo['status'], ['completed','closed']) ? $mo['end'] : null,
                    'created_by' => $mo['created_by'],
                    'approved_by' => $mo['approved_by'] ?? null,
                    'approved_at' => $mo['approved_at'] ?? null,
                ]
            );
        }
    }

    // ============================================
    // 8. 质检单（覆盖多种类型和结果）
    // ============================================
    private function createQualityInspections(): void
    {
        $qualityId = $this->users['quality_manager']->id ?? 6;
        $adminId = $this->users['admin']->id ?? 1;
        $year = date('Y');

        $qiData = [
            // 1. IQC 来料检验 - pass
            ['no'=>"QI-{$year}-002001", 'type'=>'iqc', 'item'=>'M-001', 'batch'=>'B20260701', 'date'=>'2026-07-02', 'sample'=>50, 'insp'=>50, 'qual'=>49, 'rej'=>1, 'result'=>'pass', 'status'=>QualityInspectionStatus::Completed->value, 'created_by'=>$qualityId],
            // 2. IQC 来料检验 - fail
            ['no'=>"QI-{$year}-002002", 'type'=>'iqc', 'item'=>'M-004', 'batch'=>'B20260702', 'date'=>'2026-07-03', 'sample'=>30, 'insp'=>30, 'qual'=>22, 'rej'=>8, 'result'=>'fail', 'status'=>QualityInspectionStatus::Completed->value, 'created_by'=>$qualityId],
            // 3. IPQC 过程检验 - pass
            ['no'=>"QI-{$year}-002003", 'type'=>'ipqc', 'item'=>'S-001', 'batch'=>'B20260705', 'date'=>'2026-07-05', 'sample'=>20, 'insp'=>20, 'qual'=>19, 'rej'=>1, 'result'=>'pass', 'status'=>QualityInspectionStatus::Completed->value, 'created_by'=>$qualityId],
            // 4. FQC 成品检验 - conditional
            ['no'=>"QI-{$year}-002004", 'type'=>'fqc', 'item'=>'F-001', 'batch'=>'B20260706', 'date'=>'2026-07-06', 'sample'=>10, 'insp'=>10, 'qual'=>8, 'rej'=>2, 'result'=>'conditional', 'status'=>QualityInspectionStatus::Completed->value, 'created_by'=>$qualityId],
            // 5. OQC 出货检验 - pass
            ['no'=>"QI-{$year}-002005", 'type'=>'oqc', 'item'=>'F-002', 'batch'=>'B20260710', 'date'=>'2026-07-10', 'sample'=>10, 'insp'=>10, 'qual'=>10, 'rej'=>0,  'result'=>'pass', 'status'=>QualityInspectionStatus::Completed->value, 'created_by'=>$qualityId],
            // 6. IQC - waived（免检）
            ['no'=>"QI-{$year}-002006", 'type'=>'iqc', 'item'=>'P-001', 'batch'=>'B20260708', 'date'=>'2026-07-08', 'sample'=>0,  'insp'=>0,  'qual'=>0, 'rej'=>0,  'result'=>'waived', 'status'=>QualityInspectionStatus::Completed->value, 'created_by'=>$qualityId],
        ];

        foreach ($qiData as $qi) {
            $item = $this->items[$qi['item']];
            $inspection = QualityInspection::updateOrCreate(
                ['inspection_no' => $qi['no']],
                [
                    'inspection_type' => $qi['type'],
                    'item_id' => $item->id,
                    'batch_number' => $qi['batch'],
                    'inspection_date' => $qi['date'],
                    'sample_size' => $qi['sample'],
                    'inspected_size' => $qi['insp'],
                    'qualified_size' => $qi['qual'],
                    'rejected_size' => $qi['rej'],
                    'result' => $qi['result'],
                    'status' => $qi['status'],
                    'created_by' => $qi['created_by'],
                    'approved_by' => $qi['result'] ? $adminId : null,
                    'approved_at' => $qi['result'] ? now() : null,
                ]
            );

            // 为有缺陷的质检创建行项目
            if ($qi['rej'] > 0 && $inspection->lines()->count() == 0) {
                QualityInspectionLine::create([
                    'quality_inspection_id' => $inspection->id,
                    'line_number' => 10,
                    'inspection_item' => $qi['type'] == 'iqc' ? '外观检查' : '尺寸检测',
                    'specification' => $qi['type'] == 'iqc' ? '表面无划痕' : '公差±0.1mm',
                    'result' => 'fail',
                    'judgment' => $qi['result'] == 'conditional' ? 'rework' : 'reject',
                    'remark' => $qi['type'] == 'iqc' ? '来料表面有划痕' : '外观瑕疵',
                ]);
            }
        }
    }

    // ============================================
    // 9. 库存数据（期初库存 + 交易流水）
    // ============================================
    private function createInventoryData(): void
    {
        $adminId = $this->users['admin']->id ?? 1;
        $warehouseId = $this->users['warehouse_manager']->id ?? 5;

        // 为每种物料在各仓库创建库存余额
        $balances = [
            // item, warehouse, qty, status
            ['M-001', 'WH-RAW', 1500.00, 'qualified'],
            ['M-002', 'WH-RAW', 800.00,  'qualified'],
            ['M-003', 'WH-RAW', 600.00,  'qualified'],
            ['M-004', 'WH-RAW', 250.00,  'qualified'],
            ['M-005', 'WH-RAW', 120.00,  'qualified'],
            ['M-004', 'WH-QC',  50.00,   'pending'],     // 待检物料
            ['S-001', 'WH-SF',  45.00,   'qualified'],
            ['S-002', 'WH-SF',  35.00,   'qualified'],
            ['F-001', 'WH-FG',  25.00,   'qualified'],
            ['F-002', 'WH-FG',  15.00,   'qualified'],
            ['F-001', 'WH-QC',  5.00,    'pending'],     // 待检成品
            ['P-001', 'WH-RAW', 500.00,  'qualified'],
            ['P-002', 'WH-RAW', 400.00,  'qualified'],
            ['M-002', 'WH-RAW', 50.00,   'frozen'],       // 冻结库存
        ];

        $year = date('Y');
        $txCounter = 2000;

        foreach ($balances as $bal) {
            $item = $this->items[$bal[0]];
            $wh = $this->warehouses[$bal[1]];
            $loc = $this->locations[array_key_first($this->locations)] ?? null;

            // 创建库存余额
            $balance = InventoryBalance::updateOrCreate(
                [
                    'item_id' => $item->id,
                    'warehouse_id' => $wh->id,
                    'quality_status' => $bal[3],
                ],
                [
                    'quantity' => $bal[2],
                    'reserved_quantity' => 0,
                    'frozen_quantity' => $bal[3] === 'frozen' ? $bal[2] : 0,
                    'location_id' => $loc ? $loc->id : null,
                    'created_by' => $adminId,
                ]
            );

            // 为每种库存创建对应的入库交易记录
            $txNo = "TR-{$year}-" . str_pad($txCounter++, 6, '0', STR_PAD_LEFT);

            // 确定交易类型
            $type = match(true) {
                str_starts_with($bal[0], 'F-') || str_starts_with($bal[0], 'S-') => 'production_receipt',
                $bal[3] === 'pending' => 'purchase_receive',
                $bal[3] === 'frozen' => 'freeze',
                default => 'purchase_receive',
            };

            InventoryTransaction::updateOrCreate(
                ['transaction_no' => $txNo],
                [
                    'transaction_type' => $type,
                    'item_id' => $item->id,
                    'warehouse_id' => $wh->id,
                    'location_id' => $loc ? $loc->id : null,
                    'quantity' => $bal[2],
                    'quality_status' => $bal[3],
                    'source_document_type' => $type === 'production_receipt' ? 'production_order' : ($type === 'freeze' ? 'manual' : 'purchase_order'),
                    'remark' => '期初库存导入',
                    'created_by' => $adminId,
                ]
            );
        }

        // 额外创建一些多样化的交易记录（调拨、调整、消耗等）
        $extraTxs = [
            ['type'=>'transfer',      'item'=>'M-001', 'wh'=>'WH-RAW', 'qty'=>-50,  'date'=>'2026-07-02', 'remark'=>'调拨至2号仓库'],
            ['type'=>'transfer',      'item'=>'M-001', 'wh'=>'WH-RAW', 'qty'=>50,   'date'=>'2026-07-02', 'remark'=>'从1号仓库调入'],
            ['type'=>'production_issue','item'=>'M-001','wh'=>'WH-RAW', 'qty'=>-200, 'date'=>'2026-07-03', 'remark'=>'生产工单 MO-2026-002003 领料'],
            ['type'=>'production_issue','item'=>'M-002','wh'=>'WH-RAW', 'qty'=>-100, 'date'=>'2026-07-03', 'remark'=>'生产工单 MO-2026-002003 领料'],
            ['type'=>'adjust',         'item'=>'M-003','wh'=>'WH-RAW', 'qty'=>-10,  'date'=>'2026-07-04', 'remark'=>'盘点调整'],
            ['type'=>'stocktake',      'item'=>'M-004','wh'=>'WH-RAW', 'qty'=>0,     'date'=>'2026-07-05', 'remark'=>'周期盘点（无差异）'],
            ['type'=>'production_receipt','item'=>'F-001','wh'=>'WH-FG','qty'=>15,  'date'=>'2026-07-06', 'remark'=>'生产工单 MO-2026-002004 入库'],
            ['type'=>'sales_issue',    'item'=>'F-001', 'wh'=>'WH-FG', 'qty'=>-5,   'date'=>'2026-07-07', 'remark'=>'销售出库 SO-2026-002005'],
            ['type'=>'scrap',          'item'=>'M-005', 'wh'=>'WH-RAW','qty'=>-2,   'date'=>'2026-07-08', 'remark'=>'报废处理'],
        ];

        foreach ($extraTxs as $tx) {
            $item = $this->items[$tx['item']];
            $wh = $this->warehouses[$tx['wh']];
            $txNo = "TR-{$year}-" . str_pad($txCounter++, 6, '0', STR_PAD_LEFT);

            InventoryTransaction::updateOrCreate(
                ['transaction_no' => $txNo],
                [
                    'transaction_type' => $tx['type'],
                    'item_id' => $item->id,
                    'warehouse_id' => $wh->id,
                    'quantity' => $tx['qty'],
                    'quality_status' => 'qualified',
                    'remark' => $tx['remark'],
                    'created_by' => $warehouseId,
                ]
            );
        }
    }
}
