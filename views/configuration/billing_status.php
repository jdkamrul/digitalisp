<?php
// views/configuration/billing_status.php
?>
<style>
.page-header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; margin-bottom: 20px; border-bottom: 1px solid var(--border); }
.page-title { font-size: 20px; font-weight: 600; color: var(--text); display: flex; align-items: center; gap: 8px; margin: 0; }
.page-subtitle { font-size: 13px; color: var(--text2); font-weight: 400; margin-left: 8px; }
.breadcrumb { font-size: 12px; color: var(--text2); display: flex; align-items: center; gap: 6px; }
.breadcrumb a { color: var(--text); text-decoration: none; }
.breadcrumb i { font-size: 10px; }
.icon-title { color: var(--blue); }

.box-wrapper { background: var(--card-bg); border: 1px solid var(--border); border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.table-controls { display: flex; justify-content: space-between; align-items: center; padding: 16px; border-bottom: 1px solid var(--border); font-size: 13px; color: var(--text2); }
.table-controls select, .table-controls input { border: 1px solid var(--border); background: var(--bg2); color: var(--text); padding: 4px 8px; border-radius: 4px; font-size: 13px; outline: none; }
.table-controls select { margin: 0 4px; }
.table-controls input { margin-left: 6px; width: 180px; }

.data-table { width: 100%; border-collapse: collapse; text-align: center; }
.data-table th { background: #1e3a8a; color: #fff; padding: 12px 16px; font-weight: 600; font-size: 13px; border: 1px solid var(--border); }
.data-table td { padding: 12px 16px; border: 1px solid var(--border); font-size: 13px; color: var(--text); }
.data-table tr:nth-child(even) td { background: var(--bg2); }

.action-btn { background: none; border: none; color: #22c55e; cursor: pointer; font-size: 16px; transition: transform 0.1s; }
.action-btn:hover { transform: scale(1.1); }

.pagination-wrapper { display: flex; justify-content: space-between; align-items: center; padding: 16px; font-size: 13px; color: var(--text2); }
.pagination { display: flex; list-style: none; margin: 0; padding: 0; gap: 4px; }
.pagination li { padding: 6px 12px; border: 1px solid var(--border); border-radius: 4px; cursor: pointer; user-select: none; }
.pagination li.active { background: #3b82f6; color: #fff; border-color: #3b82f6; }
.pagination li.disabled { color: var(--text3); cursor: not-allowed; }
</style>

<div class="page-header fade-in">
    <h1 class="page-title">
        <i class="fa-solid fa-users-cog icon-title"></i> Billing Status
        <span class="page-subtitle">Configure Customer Billing Status</span>
    </h1>
    <div class="breadcrumb">
        <i class="fa-solid fa-gear"></i> Configuration 
        <i class="fa-solid fa-chevron-right"></i> Billing Status
    </div>
</div>

<div class="box-wrapper fade-in fade-in-delay-1">
    <div class="table-controls">
        <div>
            SHOW 
            <select>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select> 
            ENTRIES
        </div>
        <div>
            SEARCH: <input type="text">
        </div>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Serial</th>
                <th>Customer Billing Status</th>
                <th style="width: 30%;">Details</th>
                <th style="width: 15%;">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Left</td>
                <td></td>
                <td><button class="action-btn"><i class="fa-solid fa-pen-to-square"></i></button></td>
            </tr>
            <tr>
                <td>2</td>
                <td>Free</td>
                <td></td>
                <td><button class="action-btn"><i class="fa-solid fa-pen-to-square"></i></button></td>
            </tr>
            <tr>
                <td>3</td>
                <td>Personal</td>
                <td></td>
                <td><button class="action-btn"><i class="fa-solid fa-pen-to-square"></i></button></td>
            </tr>
            <tr>
                <td>4</td>
                <td>Inactive</td>
                <td></td>
                <td><button class="action-btn"><i class="fa-solid fa-pen-to-square"></i></button></td>
            </tr>
            <tr>
                <td>5</td>
                <td>Active</td>
                <td></td>
                <td><button class="action-btn"><i class="fa-solid fa-pen-to-square"></i></button></td>
            </tr>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <div>Showing 1 to 5 of 5 entries</div>
        <ul class="pagination">
            <li class="disabled">Previous</li>
            <li class="active">1</li>
            <li class="disabled">Next</li>
        </ul>
    </div>
</div>
