/**
 * Created by Administrator on 2017/11/8 0008.
 */
/*用于一个节点只对应只有一条数据*/
/*ecahrts数据及配置处理*/
/*处理数据格式name为横坐标的键名，value为数据展示键名*/
/*除雷达图且部分组的数据处理*/
function formateNoGroupData(data,name,value){
  let categories=[];
  let datas=[];
  for(let i=0;i<data.length;i++){
    categories.push(data[i][name] || '');
    datas.push({name:data[i][name] || '',value:data[i][value] || 0});
  }
  return {categories:categories,data:datas}
}
/*配置信息统一修改*/
let ChartOptionTemplates={
  CommonOption:{
    tooltip : {
      trigger: 'axis',
        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
        type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
      }
    },
  },
  CommonLineOption: {//通用的折线图表的基本配置

    tooltip: {

      trigger: 'axis',
        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
        type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
      }

    }
  },
  Pie(data,tooltipName,name,value) {//data:数据格式：{name：xxx,value:xxx}...tooltipName为hover提示信息标题
    let pie_datas = formateNoGroupData(data,name,value);

    let option = {

      tooltip: {

        trigger: 'item',

        formatter: '{b} : {c} ({d}/%)',

        show: true

      },

      legend: {
        /*图例*/
        orient: 'vertical',

        x: 'left',

        // data: pie_datas.categories

      },

      series: [

        {

          name: tooltipName || "",

          type: 'pie',

          radius: '65%',

          center: ['50%', '50%'],

          data: pie_datas.data,
          itemStyle: {
            emphasis: {
              shadowBlur: 10,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          }

        }

      ]

    };

    return $.extend({},ChartOptionTemplates.CommonOption, option);

  },
  Line(data,tooltipName,name,value){
    let line_datas = formateNoGroupData(data,name,value);
    let option={
      tooltip: {
        trigger: 'axis',
        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
          type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
        }
      },
      legend: {
        /*图例*/
        orient: 'vertical',
        x: 'left',
        // data:line_datas.categories
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
      },
      toolbox: {
      },
      xAxis: {
        type: 'category',
        boundaryGap: false,
        data: line_datas.categories
      },
      yAxis: {
        type: 'value'
      },
      series: [
        {
          name:tooltipName || '',
          type:'line',
          data:line_datas.data
        }
      ]
    };
    return $.extend({}, ChartOptionTemplates.CommonOption, option);
  },
  /*柱状图*/
  Bar(data,tooltipName,name,value){
    let bar_datas = formateNoGroupData(data,name,value);
    let option={
      tooltip: {
        trigger: 'axis',
        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
          type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
        }
      },
      legend: {
        /*图例*/
        orient: 'vertical',
        x: 'left',
        // data:line_datas.categories
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
      },
      toolbox: {
      },
      xAxis: {
        type: 'category',
        axisTick: {
          alignWithLabel: true
        },
        data: bar_datas.categories
      },
      yAxis: {
        type: 'value'
      },
      series: [
        {
          name:tooltipName || '',
          type:'bar',
          barWidth:'60%',
          data:bar_datas.data
        }
      ]
    };
    return $.extend({}, ChartOptionTemplates.CommonOption, option);
  },
  /*条形图*/
  BarChart(data,tooltipName,name,value){
    let bar_datas = formateNoGroupData(data,name,value);
    let option={
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow'
        }
      },
      legend: {
        /*图例*/
        orient: 'vertical',
        x: 'left',
        // data:line_datas.categories
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
      },
      xAxis: {
        type: 'value',
        boundaryGap: [0, 0.01]
      },
      yAxis: {
        type: 'category',
        data: bar_datas.categories
      },
      series: [
        {
          name:tooltipName || '',
          type:'bar',
          barWidth:'60%',
          data:bar_datas.data
        }
      ]
    };
    return $.extend({}, ChartOptionTemplates.CommonOption, option);
  },
  /*雷达图*/
  Radar(data,tooltipName,name,value){
    let radar_datas = formateNoGroupData(data,tooltipName,name,value);
    let option={
      tooltip: {},
      legend: {
        /*图例*/
        orient: 'vertical',
        x: 'left',
        // data:line_datas.categories
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
      },
      radar: {
        // shape: 'circle',
        name: {
          textStyle: {
            color: '#fff',
            backgroundColor: '#999',
            borderRadius: 3,
            padding: [3, 5]
          }
        },
        indicator:radar_datas.categories
      },
      series: [
        {
          name:tooltipName || '',
          type:'radar',
          data:radar_datas.data
        }
      ]
    };
    return $.extend({}, ChartOptionTemplates.CommonOption, option);
  },
};
export default {
  ChartOptionTemplates:ChartOptionTemplates

}


