@extends('layouts.admin')

@section('title', 'Thống kê nạp tiền')

@section('content')
    <!-- Tiêu đề -->
    <h2>Thống kê nạp tiền</h2>

    <!-- Bộ lọc thời gian -->
    <div class="filter-container">
        <label for="time-range">Chọn khoảng thời gian:</label>
        <select id="time-range" onchange="updateChart()">
            <option value="7days">7 ngày gần nhất</option>
            <option value="30days">30 ngày gần nhất</option>
            <option value="90days">90 ngày gần nhất</option>
            <option value="all">Tất cả thời gian</option>
        </select>
    </div>

    <!-- Khung hình chứa biểu đồ -->
    <div class="chart-frame">
        <canvas id="myChart"></canvas>
    </div>

    <!-- Thẻ ẩn để lưu trữ dữ liệu JSON -->
    <div id="chart-data" 
         style="display: none;"
         data-dates="{{ json_encode($dates ?? []) }}"
         data-deposits="{{ json_encode($depositData ?? []) }}">
    </div>

    <style>
    .filter-container {
        text-align: center;
        margin-bottom: 20px;
    }

    .chart-frame {
        width: 80%;
        max-width: 1000px;
        height: 500px;
        background-color: #fff;
        border: 5px solid #ff0000;
        padding: 20px;
        margin: 20px auto;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    #myChart {
        width: 100% !important;
        height: 100% !important;
    }

    h2 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #1e272e;
        text-align: center;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Mảng màu cố định cho các đường biểu đồ
    const colors = [
        { border: 'red', background: 'rgba(255, 0, 0, 0.5)' },
        { border: 'blue', background: 'rgba(0, 0, 255, 0.5)' },
        { border: 'green', background: 'rgba(0, 255, 0, 0.5)' },
        { border: 'purple', background: 'rgba(128, 0, 128, 0.5)' },
        { border: 'orange', background: 'rgba(255, 165, 0, 0.5)' }
    ];

    // Lấy dữ liệu từ thuộc tính data-*
    const chartDataElement = document.getElementById('chart-data');
    const allDates = JSON.parse(chartDataElement.getAttribute('data-dates'));
    const allDepositData = JSON.parse(chartDataElement.getAttribute('data-deposits'));

    // Khởi tạo biến toàn cục cho biểu đồ
    let myChart;

    // Hàm lọc dữ liệu theo khoảng thời gian
    function filterData(range) {
        let filteredDates = [];
        let filteredDepositData = {};

        const today = new Date();
        let cutoffDate;

        switch (range) {
            case '7days':
                cutoffDate = new Date(today.setDate(today.getDate() - 7));
                break;
            case '30days':
                cutoffDate = new Date(today.setDate(today.getDate() - 30));
                break;
            case '90days':
                cutoffDate = new Date(today.setDate(today.getDate() - 90));
                break;
            case 'all':
            default:
                cutoffDate = null;
                break;
        }

        for (let i = 0; i < allDates.length; i++) {
            const date = new Date(allDates[i]);
            if (!cutoffDate || date >= cutoffDate) {
                filteredDates.push(allDates[i]);
                for (let amount in allDepositData) {
                    if (!filteredDepositData[amount]) {
                        filteredDepositData[amount] = [];
                    }
                    filteredDepositData[amount].push(allDepositData[amount][i]);
                }
            }
        }

        return { dates: filteredDates, depositData: filteredDepositData };
    }

    // Hàm vẽ hoặc cập nhật biểu đồ
    function drawChart(filteredData) {
        const datasets = [];
        let colorIndex = 0;

        for (let amount in filteredData.depositData) {
            const color = colors[colorIndex % colors.length];
            datasets.push({
                label: `Nạp ${Number(amount).toLocaleString('vi-VN')} VND`,
                data: filteredData.depositData[amount],
                fill: false,
                borderColor: color.border,
                backgroundColor: color.background,
            });
            colorIndex++;
        }

        const data = {
            labels: filteredData.dates,
            datasets: datasets
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Ngày',
                            color: '#911',
                            font: {
                                family: 'Comic Sans MS',
                                size: 16,
                                weight: 'bold',
                                lineHeight: 1.2,
                            },
                            padding: { top: 10, left: 0, right: 0, bottom: 0 }
                        },
                        ticks: {
                            callback: function(value, index, values) {
                                const date = new Date(data.labels[index]);
                                return date.toLocaleDateString('vi-VN'); // Định dạng ngày/tháng/năm
                            }
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Số lần nạp',
                            color: '#191',
                            font: {
                                family: 'Times',
                                size: 16,
                                style: 'normal',
                                lineHeight: 1.2
                            },
                            padding: { top: 10, left: 0, right: 0, bottom: 0 }
                        }
                    }
                }
            }
        };

        const ctx = document.getElementById('myChart').getContext('2d');
        if (myChart) {
            myChart.destroy(); // Hủy biểu đồ cũ trước khi vẽ lại
        }
        myChart = new Chart(ctx, config);
    }

    // Hàm cập nhật biểu đồ khi thay đổi bộ lọc
    function updateChart() {
        const range = document.getElementById('time-range').value;
        const filteredData = filterData(range);
        drawChart(filteredData);
    }

    // Vẽ biểu đồ ban đầu với toàn bộ dữ liệu
    window.onload = function() {
        const initialData = filterData('all');
        drawChart(initialData);
    };
    </script>
@endsection