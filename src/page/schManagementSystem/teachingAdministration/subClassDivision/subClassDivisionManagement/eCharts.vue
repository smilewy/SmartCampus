<template>
  <div :id="ids" style="width: 100%;height:400px;"></div>
</template>
<script>
  import echarts from 'echarts/lib/echarts'
  // 引入柱状图
  import 'echarts/lib/chart/pie'  //饼图
  import 'echarts/lib/chart/bar'  //柱状图
  // 引入提示框和标题组件
  import 'echarts/lib/component/tooltip'
  import 'echarts/lib/component/title'
  import 'echarts/lib/component/legendScroll'
  export default{
    props: ['options', 'ids'],
    data(){
      return {
        option: this.options,
        chart: ''
      }
    },
    mounted: function () {
      this.init();
    },
    watch: {
      option: {
        handler: function (newVal, oldVal) { // 监听外部传入的值,渲染新的的图表数据
          if (this.chart) {
            if (newVal) {
              this.chart.setOption({
                series: {
                  data: [{value: 12, name: '已填报'},
                    {value: 5, name: '未填报'}]
                }
              });
            } else {
              this.chart.setOption(oldVal);
            }
            this.chart.resize();
          } else {
            setTimeout(() => {
              this.init();
            }, 300);
          }
        },
        deep: true
      }
    },
    methods: {
      init () { // 初始化图表
        this.chart = echarts.init(document.getElementById(this.ids));
        this.chart.setOption(this.option);
//        window.addEventListener('resize', this.chart.resize) // 图表响应大小的关键,重绘
      },
    }
  }
</script>
<style>

</style>
