fetch("database/dashboard-data.php")
  .then((res) => res.json())
  .then((data) => {
    let statusData = [];

    data.status.forEach((row) => {
      statusData.push({
        name: row.stats,
        y: parseInt(row.total),
      });
    });

    Highcharts.chart("orderStatusChart", {
      chart: { type: "pie" },
      title: { text: "" },
      tooltip: {
        pointFormat: "Orders: <b>{point.y}</b><br/>Share: <b>{point.percentage:.1f}%</b>",
      },
      series: [
        {
          name: "Orders",
          data: statusData,
        },
      ],
    });

    let supplierNames = [];
    let supplierCounts = [];

    data.supplier.forEach((row) => {
      supplierNames.push(row.supplier_name);
      supplierCounts.push(parseInt(row.total));
    });

    Highcharts.chart("supplierProductChart", {
      chart: { type: "column" },
      title: { text: "" },
      xAxis: {
        categories: supplierNames,
        crosshair: true,
      },
      yAxis: {
        title: { text: "Total Orders" },
      },
      tooltip: {
        useHTML: true,
        formatter: function () {
          const total = this.series.data.reduce((sum, point) => sum + point.y, 0);
          const percent = total === 0 ? 0 : ((this.y / total) * 100).toFixed(1);
          const supplierName = this.point.category || this.key || "";

          return `
            <div style="padding:8px;">
              <b>${supplierName}</b><br/>
              Orders: <b>${this.y}</b><br/>
              Share: <b>${percent}%</b>
            </div>
          `;
        },
      },
      series: [
        {
          name: "Orders",
          data: supplierCounts,
        },
      ],
    });

    let days = [];
    let deliveries = [];

    data.delivery.forEach((row) => {
      days.push(row.day);
      deliveries.push(parseInt(row.total));
    });

    Highcharts.chart("deliveryHistoryChart", {
      chart: { type: "line" },
      title: { text: "" },
      xAxis: { categories: days },
      tooltip: {
        shared: true,
      },
      yAxis: {
        title: { text: "Product Delivered" },
      },
      series: [
        {
          name: "Product Delivered",
          data: deliveries,
        },
      ],
    });
  });
