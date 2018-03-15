<template>
  <div class="studentVolunteerCount">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb"><span class="breadcrumb_active">志愿填报情况</span><span>|</span><router-link
        :to="{name:'volunteerDistributionStatus',params:{planId:pId}}"
        tag="span">志愿分布情况</router-link></span>
    </el-row>
    <el-row class="subClassDivision_row">
      <el-col :span="8" class="chartsName">
        <span class="exam_subTitle">学生填报志愿进度图</span>
        <div id="progress" style="width: 100%;height:400px;"></div>
      </el-col>
      <el-col :span="8" class="chartsName">
        <span class="exam_subTitle">各科类学生志愿分布图</span>
        <div id="distribution" style="width: 100%;height:400px;"></div>
      </el-col>
      <el-col :span="8" class="chartsName">
        <span class="exam_subTitle">各专业学生志愿分布图</span>
        <div id="volunteer" style="width: 100%;height:400px;"></div>
      </el-col>
    </el-row>
    <el-row class="subClassDivision_row">
      <el-row class="chartsName">
        <span class="exam_subTitle">各班志愿填报进度统计图</span>
        <div style="width: 80%;margin: auto;">
          <div id="volunProgress" style="width: 100%;height:400px;"></div>
        </div>
      </el-row>
    </el-row>
    <el-row class="subClassDivision_row">
      <el-row class="chartsName">
        <span class="exam_subTitle">各班志愿分布图—科类</span>
        <div style="width: 80%;margin: auto;">
          <div id="volunSubject" style="width: 100%;height:400px;"></div>
        </div>
      </el-row>
    </el-row>
    <el-row class="subClassDivision_row">
      <el-row class="chartsName">
        <span class="exam_subTitle">各班志愿分布图—专业</span>
        <div style="width: 80%;margin: auto;">
          <div id="volunMajor" style="width: 100%;height:400px;"></div>
        </div>
      </el-row>
    </el-row>
  </div>
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
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        progress: {
          tooltip: {
            trigger: 'item',
            formatter: "{b} : {d}%"  //{a} <br/>{b}: {c} ({d}%)
          },
          legend: {
            orient: 'vertical',
            x: 'left',
            data: [{
              name: '已填报',
              icon: 'circle'
            }, {
              name: '未填报',
              icon: 'circle'
            }]
          },
          series: [
            {
              name: '访问来源',
              type: 'pie',
              radius: ['32%', '50%'],
              avoidLabelOverlap: false,
              label: {
                normal: {
                  show: false,
                  position: 'center'
                }
              },
              labelLine: {
                normal: {
                  show: false
                }
              },
              data: []
            }
          ]
        },
        distribution: {
          tooltip: {
            trigger: 'item',
            formatter: "{b} : {d}%"  //{a} <br/>{b}: {c} ({d}%)
          },
          legend: {
            orient: 'vertical',
            x: 'left',
            data: []
          },
          series: [
            {
              name: '访问来源',
              type: 'pie',
              radius: ['32%', '50%'],
              avoidLabelOverlap: false,
              label: {
                normal: {
                  show: false,
                  position: 'center'
                }
              },
              labelLine: {
                normal: {
                  show: false
                }
              },
              data: []
            }
          ]
        },
        volunteer: {
          tooltip: {
            trigger: 'item',
            formatter: "{b} : {d}%"  //{a} <br/>{b}: {c} ({d}%)
          },
          legend: {
            orient: 'vertical',
            x: 'left',
            data: []
          },
          series: [
            {
              name: '访问来源',
              type: 'pie',
              radius: ['32%', '50%'],
              avoidLabelOverlap: false,
              label: {
                normal: {
                  show: false,
                  position: 'center'
                }
              },
              labelLine: {
                normal: {
                  show: false
                }
              },
              data: []
            }
          ]
        },
        volunProgress: {
          legend: {
            show: true,
            x: 'left',
            y: 'top',
            data: ['已填报', '未填报']
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
              type: 'line'        // 默认为直线，可选为：'line' | 'shadow'
            },
            formatter: "{b} <br/>{a0}: {c0}%<br/>{a1}: {c1}%"  //{a} <br/>{b}: {c} ({d}%)
          },
          grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
          },
          xAxis: [
            {
              type: 'category',
              data: [],
              axisTick: {
                alignWithLabel: true
              }
            }
          ],
          yAxis: [
            {
              type: 'value',
              axisLabel: {
                show: true,
                interval: 'auto',
                formatter: '{value}%'
              },
              max: '100',
              show: true
            }
          ],
          series: [
            {
              name: '已填报',
              type: 'bar',
              barWidth: '40',
              stack: '已填报',
              data: []
            },
            {
              name: '未填报',
              type: 'bar',
              barWidth: '40',
              stack: '已填报',
              data: []
            }
          ]
        },
        volunSubject: {
          legend: {
            show: true,
            x: 'left',
            y: 'top',
            data: []
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
              type: 'line'        // 默认为直线，可选为：'line' | 'shadow'
            },
            formatter: ""  //{a} <br/>{b}: {c} ({d}%)
          },
          grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
          },
          xAxis: [
            {
              type: 'category',
              data: [],
              axisTick: {
                alignWithLabel: true
              }
            }
          ],
          yAxis: [
            {
              type: 'value',
              axisLabel: {
                show: true,
                interval: 'auto',
                formatter: '{value}人'
              },
              show: true
            }
          ],
          series: []
        },
        volunMajor: {
          legend: {
            show: true,
            x: 'left',
            y: 'top',
            data: []
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
              type: 'line'        // 默认为直线，可选为：'line' | 'shadow'
            },
            formatter: ""  //{a} <br/>{b}: {c} ({d}%)
          },
          grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
          },
          xAxis: [
            {
              type: 'category',
              data: [],
              axisTick: {
                alignWithLabel: true
              }
            }
          ],
          yAxis: [
            {
              type: 'value',
              axisLabel: {
                show: true,
                interval: 'auto',
                formatter: '{value}人'
              },
              show: true
            }
          ],
          series: []
        },
        pId: ''
      }
    },
    mounted: function () {
    },
    created: function () {
      var self = this, data, chart1, chart2, chart3, chart4, chart5, chart6;
      self.pId = self.$route.params.planId;
      data = {
        planId: self.pId,
        sort: 1
      };
      req.ajaxSend('/school/DivideBranch/wishStatistics', 'post', data, function (res) {
        self.progress.series[0].data = [{
          value: res.wishStu.al,
          name: '已填报'
        }, {
          value: res.wishStu.not,
          name: '未填报'
        }];
        for (let name in res.wishStuBra) {
          self.distribution.legend.data.push({
            name: name,
            icon: 'circle'
          });
          self.distribution.series[0].data.push({
            name: name,
            value: res.wishStuBra[name]
          })
        }
        for (let name in res.wishStuMaj) {
          self.volunteer.legend.data.push({
            name: name,
            icon: 'circle'
          });
          self.volunteer.series[0].data.push({
            name: name,
            value: res.wishStuMaj[name]
          })
        }
        for (let obj of res.wishClass) {
          self.volunProgress.xAxis[0].data.push(obj.name);
          self.volunProgress.series[0].data.push(obj.alr);
          self.volunProgress.series[1].data.push(obj.not);
        }
        self.volunSubject.xAxis[0].data = res.class;
        for (let obj of res.wishClassBra) {
          self.volunSubject.legend.data.push(obj.name);
          let data = {
            name: obj.name,
            type: 'bar',
            barWidth: '40',
            stack: 'b',
            data: []
          };
          for (let n of res.class) {
            data.data.push(obj.class[n]);
          }
          self.volunSubject.series.push(data);
        }
        self.volunMajor.xAxis[0].data = res.class;
        for (let obj of res.wishClassMaj) {
          self.volunMajor.legend.data.push(obj.name);
          let data = {
            name: obj.name,
            type: 'bar',
            barWidth: '40',
            stack: 'a',
            data: []
          };
          for (let n of res.class) {
            data.data.push(obj.class[n]);
          }
          self.volunMajor.series.push(data);
        }
        chart1 = echarts.init(document.getElementById('progress'));
        chart2 = echarts.init(document.getElementById('distribution'));
        chart3 = echarts.init(document.getElementById('volunteer'));
        chart4 = echarts.init(document.getElementById('volunProgress'));
        chart5 = echarts.init(document.getElementById('volunSubject'));
        chart6 = echarts.init(document.getElementById('volunMajor'));
        chart1.setOption(self.progress);
        chart2.setOption(self.distribution);
        chart3.setOption(self.volunteer);
        chart4.setOption(self.volunProgress);
        chart5.setOption(self.volunSubject);
        chart6.setOption(self.volunMajor);
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      }
    }
  }
</script>
<style>
  .studentVolunteerCount .chartsName .exam_subTitle {
    display: inline-block;
    width: 12.5rem;
    height: 2rem;
    line-height: 2rem;
    padding: 0;
    border-radius: 0 15px 15px 0;
    -webkit-box-shadow: 0 5px 5px 0 #ddd;
    -moz-box-shadow: 0 5px 5px 0 #ddd;
    box-shadow: 0 5px 5px 0 #ddd;
    background-color: #89bcf5;
    border-color: #89bcf5;
    color: #fff;
    text-align: center;
    font-size: .875rem;
  }

  .studentVolunteerCount #progress, .studentVolunteerCount #distribution, .studentVolunteerCount #volunteer, .studentVolunteerCount #volunProgress, .studentVolunteerCount #volunSubject, .studentVolunteerCount #volunMajor {
    margin-top: 1.8rem;
  }
</style>
