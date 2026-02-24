{*
    Params:
    piechart    - piechart object
    headline    - string
    id          - string
    width       - string
    height      - string
*}

{if $piechart->getActive()}
    <div id="{$id}" style="background: {$chartbg|default:'#fff'}; width: {$width}; height: {$height}; padding: {$chartpad|default:'0'};">
        <canvas id="{$id}_canvas" style="margin: 0 auto"></canvas>
    </div>
    <script>
        $(document).ready(function() {
            let seriesData   = {$piechart->getSeriesJSON()};
            let data         = seriesData[0].data.map(item => item[1]);
            let sum          = data.reduce((partialSum, a) => partialSum + a, 0);
            let labels       = seriesData[0].data.map(item => item[0]);

            let chart = new Chart(
                document.getElementById('{$id}_canvas'),
                {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{ data }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: '{$headline}',
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        // console.log(context);
                                        return ' ' + (context.raw * 100 / sum).toFixed(1) + ' %';
                                    }
                                }
                            }
                        }
                    }
                }
            );
        });
    </script>
{else}
    <div class="alert alert-info" role="alert">{__('statisticNoData')}</div>
{/if}