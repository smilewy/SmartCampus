<template>
  <div class="personalResultsContrast">
    <el-row type="flex" align="middle">
      <h3>学生个人成绩</h3>
      <span class="l_gap">
        <router-link tag="span" to="/personalResultsQuery" class="personalResultsContrast_bread">成绩查询</router-link>
        <span class="personalResultsContrast_bread active">成绩对比</span>
      </span>
    </el-row>
    <el-row class="scoreRow scoreRowOne">
      <el-form :inline="true" class="formInline">
        <el-form-item label="考试：">
          <el-select v-model="selectParam.examinationid1" placeholder="请选择" class="test">
            <el-option
              v-for="item in examList"
              :key="item.examinationid"
              :label="item.examination"
              :value="item.examinationid">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="对比考试：">
          <el-select v-model="selectParam.examinationid2" placeholder="请选择" class="test">
            <el-option
              v-for="item in examList"
              :key="item.examinationid"
              :label="item.examination"
              :value="item.examinationid">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item class="scoreQuery_btn">
          <el-button type="primary" @click="search">
            <img
              src="../../../../../assets/img/schManagementSystem/teachingAdministration/studentScores/icon_search.png"
              alt="">
            <span>查询</span>
          </el-button>
        </el-form-item>
      </el-form>
      <p>
        <span>姓名：{{userInfo.name}}</span>
        <span class="l_gap">年级：{{userInfo.grade}}</span>
        <span class="l_gap">班级：{{userInfo.className}}</span>
      </p>
    </el-row>
    <el-row type="flex" justify="center">
      <el-col :span="20">
        <h5>成绩对比图</h5>
        <el-row>
          <div id="progress1" style="width: 100%;height:400px;"></div>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import echarts from 'echarts/lib/echarts'
  // 引入柱状图
  import 'echarts/lib/chart/line'  //雷达图
  import 'echarts/lib/chart/bar'  //柱状图
  // 引入提示框和标题组件
  import 'echarts/lib/component/tooltip'
  import 'echarts/lib/component/title'
  import 'echarts/lib/component/legendScroll'
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        examList: [],
        userInfo: {},
        scoreInfo: {},
        selectParam: {
          examinationid1: '',
          examinationid2: ''
        },
        chart1: '',
        option1: {
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'cross'
            }
          },
          grid: {
            right: '20%'
          },
          toolbox: {
            feature: {
              dataView: {show: true, readOnly: false},
              restore: {show: true},
              saveAsImage: {show: true}
            }
          },
          legend: {
            data: []
          },
          xAxis: [
            {
              type: 'category',
              axisTick: {
                alignWithLabel: true
              },
              data: []
            }
          ],
          yAxis: [
            {
              type: 'value',
              name: '分数',
              min: 0,
              axisLabel: {
                formatter: '{value} 分'
              }
            },
            {
              type: 'value',
              name: '班级名次',
              min: 0,
              max: 0,
              position: 'right',
              offset: 80,
              axisLabel: {
                formatter: '{value} 名'
              }
            },
            {
              type: 'value',
              name: '年级名次',
              min: 0,
              max: 0,
              position: 'right',
              axisLabel: {
                formatter: '{value} 名'
              }
            }
          ],
          series: []
        }
      }
    },
    mounted: function () {
      this.chart1 = echarts.init(document.getElementById('progress1'));
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/Achievement/personal/type/user', 'post', '', function (res) {
        self.userInfo = res;
        self.examList = res.examinationlist;
      })
    },
    methods: {
      search(){
        if (!this.selectParam.examinationid1 || !this.selectParam.examinationid2) {
          this.vmMsgWarning('请选择考试！');
          return false;
        }
        if (this.selectParam.examinationid1 == this.selectParam.examinationid2) {
          this.vmMsgWarning('对比考试不能是同一场考试！');
          return false;
        }
        let exId = {
          examinationid: []
        };
        exId.examinationid.push(this.selectParam.examinationid1);
        exId.examinationid.push(this.selectParam.examinationid2);
        this.loadData(exId);
      },
      loadData(data){
        var self = this;
        req.ajaxSend('/school/Achievement/personal/type/contrast', 'post', data, function (res) {
          let d3 = {
            name: '班级名次',
            type: 'line',
            data: []
          }, d4 = {
            name: '年级名次',
            type: 'line',
            data: []
          };
          self.scoreInfo = res;
          self.option1.xAxis[0].data = [];
          self.option1.legend.data = [];
          self.option1.series = [];
          self.option1.yAxis[1].max = res.classAll;
          self.option1.yAxis[2].max = res.gradeAll;
          for (let obj of res.data) {
            self.option1.xAxis[0].data.push(obj.examination);
          }
          for (let mbj of res.subjectlist) {
            let d1 = {
              name: mbj.subjectname,
              icon: 'circle'
            }, d2 = {
              name: mbj.subjectname,
              type: 'bar',
              stack: '总分',
              barWidth: 50,
              data: []
            };
            for (let obj of res.data) {
              d2.data.push(obj.value.score[mbj.subjectid]);
            }
            self.option1.series.push(d2);
            self.option1.legend.data.push(d1);
          }
          for (let obj of res.data) {
            d3.data.push(obj.value.classRanking);
            d4.data.push(obj.value.gradeRanking);
          }
          self.option1.series.push(d3);
          self.option1.series.push(d4);
          self.option1.legend.data.push({
            name: '班级名次'
          });
          self.option1.legend.data.push({
            name: '年级名次'
          });
          self.chart1.setOption(self.option1);
        })
      }
    }
  }
</script>
<style>
  .personalResultsContrast {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    font-size: 14px;
  }

  .personalResultsContrast h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .personalResultsContrast .scoreRow {
    margin: 1.125rem 0;
  }

  .personalResultsContrast .scoreRowOne {
    margin: 2rem 0 1.125rem;
  }

  .personalResultsContrast .scoreQuery_btn .el-button {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }

  .personalResultsContrast .test {
    width: 15.625rem;
  }

  .personalResultsContrast .formInline .el-form-item {
    margin-right: 2.5rem;
  }

  .personalResultsContrast .l_gap {
    margin-left: 1rem;
  }

  .personalResultsContrast .personalResultsContrast_bread {
    padding: 0 1.25rem;
    font-size: 1.125rem;
    cursor: pointer;
  }

  .personalResultsContrast .personalResultsContrast_bread + .personalResultsContrast_bread {
    border-left: 2px solid #d2d2d2;
  }

  .personalResultsContrast .personalResultsContrast_bread.active {
    color: #4da1ff;
  }

  .personalResultsContrast h5 {
    font-size: 1.5rem;
    text-align: center;
    margin: 3rem 0 1.5rem;
  }
</style>
