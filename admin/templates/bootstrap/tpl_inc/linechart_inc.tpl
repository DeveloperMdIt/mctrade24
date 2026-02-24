{*
    Params:
    linechart   - linechart object
    headline    - string
    id          - string
    width       - string
    height      - string
    ylabel      - string
    href        - bool
    legend      - bool
    ymin        - string
*}

{if $linechart->getActive()}
    <div id="{$id}" style="background: {$chartbg|default:'#fff'}; width: {$width}; height: {$height}; padding: {$chartpad|default:'0'};">
        <canvas id="{$id}_canvas"></canvas>
    </div>

    <script>
        $(document).ready(function() {
            let axisData     = {$linechart->getAxisJSON()};
            let seriesData   = {$linechart->getSeriesJSON()};
            let labels       = axisData.categories;
            let datasets     = seriesData.map((series, idx) => ({
                label: series.name,
                data: series.data.map(item => item.y),
                backgroundColor: series.lineColor,
                borderColor: series.lineColor,
                order: seriesData.length - idx,
            }));

            let maxValue              = Math.max(...datasets.map(set => Math.max(...set.data)));
            let maxItemCountPerSeries = Math.max(...datasets.map(set => set.data.length));
            labels = labels.slice(0, maxItemCountPerSeries);

            let chart = new Chart(
                document.getElementById('{$id}_canvas'),
                {
                    type: 'line',
                    data: { labels, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        clip: false,
                        plugins: {
                            legend: { display: false },
                        },
                        scales: {
                            x: {
                                clip: false,
                                ticks: {
                                    maxRotation: 0,
                                    color: axisData.labels.style.color,
                                }
                            },
                            y: {
                                clip: false,
                                {if isset($ymax) && strlen($ymax) > 0}
                                    max: {$ymax},
                                {else}
                                    max: maxValue + 1,
                                {/if}
                                {if isset($ymin) && strlen($ymin) > 0}
                                    min: {$ymin},
                                {/if}
                                title: {
                                    display: true,
                                    text: '{$ylabel}{$yunit|default:""}',
                                    color: '#5cbcf6',
                                },
                                ticks: {
                                    stepSize: 1,
                                    color: '#5cbcf6'
                                }
                            }
                        },
                        elements: {
                            line: {
                                borderJoinStyle: 'round',
                                cubicInterpolationMode: 'monotone',
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        }
                    }
                }
            );
        });
    </script>
{else}
    <div class="alert alert-info" role="alert">{__('statisticNoData')}</div>
{/if}
