Vue.component(
    'download-statistics', {

        template: '#package-download-statistics-template',

        props: ['packageId'],

        data: function() {
            return {
                title: '',
                labels: [],
                datasets: [],
                versions: [''],
                chart: null,
                legend: '',
                loading: true,
                group: 'months',
                from: {
                    year: '2015',
                    month: '01',
                    day: '01'
                },
                to: {
                    year: '2016',
                    month: '05',
                    day: '01'
                },
                version: ''

            }
        },

        ready: function() {
            var today = new Date();

            this.from.year = today.getFullYear() - 1;
            this.from.month = this.str_pad(today.getMonth() + 1);
            this.from.day = this.str_pad(today.getDate());

            this.to.year = today.getFullYear();
            this.to.month = this.str_pad(today.getMonth() + 1);
            this.to.day = this.str_pad(today.getDate());

            this.loadData();
        },

        methods: {

            refresh: function() {
                var _MS_PER_DAY = 1000 * 60 * 60 * 24;
                var date1 = new Date(this.from.month + '/' + this.from.day + '/' + this.from.year);
                var date2 = new Date(this.to.month + '/' + this.to.day + '/' + this.to.year);
                var utc1 = Date.UTC(date1.getFullYear(), date1.getMonth(), date1.getDate());
                var utc2 = Date.UTC(date2.getFullYear(), date2.getMonth(), date2.getDate());
                var diff = Math.floor((utc2 - utc1) / _MS_PER_DAY);

                if (this.group === 'days' && diff > 31) {
                    alert('Please select a time range smaller than 31 days');
                    return;
                }

                this.loading = true;

                this.loadData();
            },

            loadData: function() {
                var from = '';
                var to = '';

                if (this.group == 'months') {
                    from = this.from.year + '-' + this.from.month;
                    to = this.to.year + '-' + this.to.month;
                } else if (this.group == 'days') {
                    from = this.from.year + '-' + this.from.month + '-' + this.from.day;
                    to = this.to.year + '-' + this.to.month + '-' + this.to.day;
                }

                this.$http.get(
                    '/stats/api/' + this.packageId +
                    '?group=' + this.group +
                    '&from=' + from +
                    '&to=' + to +
                    '&version=' + this.version,
                    [],
                    []
                ).then(
                    function(response) {
                        this.title = response.data.title;
                        this.labels = response.data.labels;
                        this.datasets = response.data.datasets;
                        this.versions = response.data.allVersions;

                        this.versions.unshift('');

                        this.loading = false;

                        this.makeChart();
                    }, function(response) {
                        // error callback
                    }
                );
            },

            makeChart: function() {

                Chart.defaults.global.legend.display = false;
                //Chart.defaults.global.tooltips.mode = 'label';

                if (this.chart) {
                    this.chart.data.labels = this.labels;
                    this.chart.data.datasets = this.datasets;
                    this.chart.update();
                    this.legend = this.chart.generateLegend();
                    return;
                }

                this.chart = new Chart(
                    this.$els.canvas.getContext('2d'),
                    {
                        type: "line",
                        data: {
                            labels: this.labels,
                            datasets: this.datasets
                        },
                        options: {
                            maintainAspectRatio: true,
                            responsive: true,
                            elements: {
                                point: {
                                    radius: 3
                                },
                                line: {
                                    borderWidth: 2
                                }
                            },
                            legendCallback: function(chart) {

                                var html = '<ul class="chart-0-legend">';

                                chart.legend.legendItems.forEach(
                                    function(item) {
                                        html +=
                                            '<li><span style="background-color:' +
                                            item.strokeStyle +
                                            '"></span>' +
                                            item.text +
                                            '</li>';
                                    }
                                );

                                html += '</ul>';

                                return html;
                            }
                        }
                    }
                );

                this.legend = this.chart.generateLegend();
            },

            str_pad: function(n) {
                return String("00" + n).slice(-2);
            }
        }
    }
);

var vue = new Vue(
    {
        el: 'body'
    }
);
