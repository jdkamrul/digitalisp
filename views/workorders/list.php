<?php // views/workorders/list.php ?>
<style>
.kanban { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; align-items:start; }
.kanban-col { background:var(--bg3); border-radius:14px; padding:12px; min-height:200px; }
.kanban-header { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; }
.kanban-card { background:var(--bg2); border:1px solid var(--border); border-radius:10px; padding:12px; margin-bottom:8px; cursor:pointer; transition:all .2s; }
.kanban-card:hover { border-color:var(--blue); transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,0.2); }
.priority-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:6px; }
</style>

<div class="page-header fade-in">
    <div>
        <h1 class="page-title">Work Orders</h1>
        <div class="page-breadcrumb" style="display:flex;gap:16px;margin-top:6px;">
            <span style="color:var(--yellow);">⏳ <?= $pending ?> Pending</span>
            <span style="color:var(--blue);">🔧 <?= $inProgress ?> In Progress</span>
            <span style="color:var(--green);">✅ <?= $completed ?> Completed</span>
        </div>
    </div>
    <a href="<?= base_url('workorders/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Work Order</a>
</div>

<!-- Kanban Board -->
<div class="kanban fade-in">
    <?php
    $columns = [
        'pending'     => ['label' => 'Pending',     'color' => 'var(--yellow)', 'icon' => 'fa-clock'],
        'assigned'    => ['label' => 'Assigned',    'color' => 'var(--blue)',   'icon' => 'fa-user-check'],
        'in_progress' => ['label' => 'In Progress', 'color' => 'var(--purple)', 'icon' => 'fa-screwdriver-wrench'],
        'completed'   => ['label' => 'Completed',   'color' => 'var(--green)',  'icon' => 'fa-check'],
    ];
    $pcol = ['urgent'=>'#ef4444','high'=>'#f97316','normal'=>'#3b82f6','low'=>'#6b7280'];
    foreach($columns as $status => $col):
        $cards = array_filter($workOrders, fn($w) => $w['status'] === $status);
    ?>
    <div class="kanban-col">
        <div class="kanban-header" style="color:<?= $col['color'] ?>">
            <span><i class="fa-solid <?= $col['icon'] ?>" style="margin-right:6px;"></i><?= $col['label'] ?></span>
            <span style="background:var(--bg2);border-radius:20px;padding:2px 9px;font-size:11px;"><?= count($cards) ?></span>
        </div>
        <?php if(empty($cards)): ?>
        <div style="text-align:center;padding:24px 10px;color:var(--text2);font-size:12px;">No work orders</div>
        <?php else: foreach($cards as $wo): ?>
        <a href="<?= base_url("workorders/view/{$wo['id']}") ?>" style="text-decoration:none;">
            <div class="kanban-card">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px;">
                    <div style="font-size:12px;font-weight:700;color:var(--text);"><?= htmlspecialchars(substr($wo['title'],0,50)) ?><?= strlen($wo['title'])>50?'…':'' ?></div>
                    <span style="font-size:9px;background:rgba(0,0,0,0.2);border-radius:4px;padding:2px 6px;font-weight:700;color:<?= $pcol[$wo['priority']]??'#6b7280' ?>;white-space:nowrap;margin-left:6px;"><?= strtoupper($wo['priority']) ?></span>
                </div>
                <div style="font-size:11px;margin-bottom:6px;color:var(--text2);font-family:monospace;"><?= htmlspecialchars($wo['wo_number']) ?></div>
                <?php if($wo['customer_name']): ?>
                <div style="font-size:11px;color:var(--text2);margin-bottom:4px;"><i class="fa-solid fa-user" style="margin-right:4px;"></i><?= htmlspecialchars($wo['customer_name']) ?></div>
                <?php endif; ?>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
                    <span class="badge badge-gray" style="font-size:9px;"><?= ucfirst(str_replace('_',' ',$wo['type'])) ?></span>
                    <span style="font-size:11px;color:var(--text2);"><?= date('d M',strtotime($wo['created_at'])) ?></span>
                </div>
                <?php if($wo['technician']): ?>
                <div style="font-size:10px;color:var(--blue);margin-top:4px;"><i class="fa-solid fa-helmet-safety" style="margin-right:3px;"></i><?= htmlspecialchars($wo['technician']) ?></div>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<div style="margin-top:16px;" class="fade-in">
    <div class="card" style="overflow:hidden;">
        <div style="padding:14px 18px;font-size:13px;font-weight:700;border-bottom:1px solid var(--border);">All Work Orders — Table View</div>
        <table class="data-table">
            <thead><tr><th>WO#</th><th>Title</th><th>Customer</th><th>Type</th><th>Priority</th><th>Technician</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach($workOrders as $wo): ?>
                <tr>
                    <td style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($wo['wo_number']) ?></td>
                    <td style="max-width:200px;"><div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($wo['title']) ?></div></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($wo['customer_name']??'—') ?></td>
                    <td><span class="badge badge-gray"><?= ucfirst(str_replace('_',' ',$wo['type'])) ?></span></td>
                    <td><span class="badge" style="color:<?= $pcol[$wo['priority']]??'#6b7280' ?>;background:<?= $pcol[$wo['priority']]??'#6b7280' ?>22;"><?= ucfirst($wo['priority']) ?></span></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($wo['technician']??'—') ?></td>
                    <td><?php $wsc=['pending'=>'badge-yellow','assigned'=>'badge-blue','in_progress'=>'badge-purple','completed'=>'badge-green','cancelled'=>'badge-gray'];
                        echo '<span class="badge '.($wsc[$wo['status']]??'badge-gray').'">'.ucfirst(str_replace('_',' ',$wo['status'])).'</span>'; ?></td>
                    <td><div style="display:flex;gap:6px;">
                        <a href="<?= base_url("workorders/view/{$wo['id']}") ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-eye"></i></a>
                        <?php if(!in_array($wo['status'],['completed','cancelled'])): ?>
                        <form method="POST" action="<?= base_url("workorders/delete/{$wo['id']}") ?>" onsubmit="return confirm('Delete this work order?');" style="display:inline;">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </div></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
