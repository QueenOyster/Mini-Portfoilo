<html>
<body>

sds
<canvas id="myChartOne"></canvas>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js">
    const canvas = document.querySelector(".canvas");
    const context = canvas.getContext("2d");


    const myChartOne = document.getElementById("myChartOne").getContext("2d");

    const barChar = new Chart(myChartOne, {
        type: "bar", // pie, line, donut, polarArea ...
        data: {
            labels: [
                "봉준호",
                "타르코프스키",
                "대런애러노프스키",
                "드네 빌뇌브",
                "홍상수",
            ],
            datasets: [
                {
                    label: "영화력",
                },
            ],
        },
    });
</script>

</body>
</html>
