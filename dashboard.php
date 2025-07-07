<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ระบบจัดการพัสดุในหน่วยงาน</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
    body {
        background-color: #d6d6d6;
        font-family: 'Prompt', sans-serif;
    }

    .summary-card {
        background: #ffffff;
        border-radius: 10px;
        text-align: center;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .summary-card h4 {
        font-size: 20px;
        margin-bottom: 5px;
    }

    .summary-card .number {
        font-size: 28px;
        font-weight: bold;
    }

    .chart-container {
        width: 100%;
        max-width: 350px;
        margin: auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .warning-box {
        background-color: #fff3cd;
        border-left: 6px solid #ffc107;
        padding: 15px;
        border-radius: 8px;
    }

    .data-table td,
    .data-table th {
        vertical-align: middle !important;
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #004085; padding-left: 2rem;">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">ระบบจัดการพัสดุในหน่วยงาน</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">ระบบจัดการพัสดุในหน่วยงาน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="searchreport_student.php">ค้นหาข้อมูลนักเรียน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="record_score.php">บันทึกคะแนน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="subject.php">เกรดแต่ละรายวิชา</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container my-4">
        <!-- Summary Row -->
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="summary-card">
                    <h4>รายการทั้งหมด</h4>
                    <div class="number">256</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card" style="background-color: #ffe4cc;">
                    <h4>รายการใกล้หมดอายุ</h4>
                    <div class="number text-danger">5</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card" style="background-color: #e3ffe3;">
                    <h4>รายการเข้าใหม่</h4>
                    <div class="number text-success">12</div>
                </div>
            </div>
        </div>

        <!-- Chart and Warning -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="summary-card">
                    <h5>กราฟแสดงสัดส่วนประเภทพัสดุ</h5>
                    <div class="chart-container">
                        <canvas id="assetPieChart"></canvas>
                    </div>
                    <ul class="mt-3 text-start" style="font-size: 14px;">
                        <li>อุปกรณ์สำนักงาน 40%</li>
                        <li>ซิลิคอนกราฟิกส์ 35%</li>
                        <li>วัสดุอื่น 25%</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="warning-box">
                    <strong>แจ้งเตือน</strong>
                    <ul class="mt-2">
                        <li>โปรแกรม Adobe Premiere Pro จะหมดอายุภายใน 30 วัน</li>
                        <li>ต่ออายุโปรแกรม Chat Gbt Plus</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">รายการพัสดุล่าสุด</h5>
                <div class="table-responsive rounded">
                    <table class="table table-bordered table-hover data-table">
                        <thead class="table-dark">
                            <tr>
                                <th>ชื่อ</th>
                                <th>ระยะเวลา</th>
                                <th>ราคา</th>
                                <th>งปม.</th>
                                <th>เริ่มต้นใช้งาน</th>
                                <th>สิ้นสุดการใช้งาน</th>
                                <th>ผู้ใช้งาน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>โปรแกรม Adobe Premiere Pro</td>
                                <td>1 ปี</td>
                                <td>9,600</td>
                                <td>2566</td>
                                <td>21/2/09</td>
                                <td>20/2/10</td>
                                <td>ชาญชัย</td>
                            </tr>
                            <tr>
                                <td>ต่ออายุโปรแกรม Chat Gbt Plus</td>
                                <td>1 ปี</td>
                                <td>1,160</td>
                                <td>2567</td>
                                <td>12/6/11</td>
                                <td>11/6/11</td>
                                <td>อนวัช, ภิรดี</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
    const ctx = document.getElementById('assetPieChart').getContext('2d');

    const dataValues = [40, 35, 25];
    const total = dataValues.reduce((a, b) => a + b, 0);

    const pieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['อุปกรณ์สำนักงาน', 'ซิลิคอนกราฟิกส์', 'วัสดุอื่น'],
            datasets: [{
                data: dataValues,
                backgroundColor: ['#4e73df', '#f6c23e', '#36b9cc']
            }]
        },
        options: {
            plugins: {
                datalabels: {
                    color: '#fff',
                    formatter: (value, context) => {
                        const percentage = (value / total * 100).toFixed(0);
                        return percentage + '%';
                    },
                    font: {
                        weight: 'bold',
                        size: 14
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
    </script>

</body>

</html>