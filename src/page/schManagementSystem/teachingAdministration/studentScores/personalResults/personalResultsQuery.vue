<template>
  <div class="personalResultsQuery">
    <el-row type="flex" align="middle">
      <h3>学生个人成绩</h3>
      <span class="l_gap">
        <span class="personalResultsQuery_bread active">成绩查询</span>
        <router-link tag="span" to="/personalResultsContrast" class="personalResultsQuery_bread">成绩对比</router-link>
      </span>
    </el-row>
    <el-row class="scoreRow scoreRowOne">
      <el-form :inline="true" class="formInline">
        <el-form-item label="考试：">
          <el-select v-model="selectParam.examinationid" placeholder="请选择" class="test">
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
    <el-row>
      <el-table
        :data="tableData"
        style="width: 100%"
        border
      >
        <el-table-column
          min-width="120"
          label="">
          <template slot-scope="scope">
            <span v-if="scope.row.name=='classscore'">个人成绩</span>
            <span v-if="scope.row.name=='classRanking'">班名次</span>
            <span v-if="scope.row.name=='gradeRanking'">级名次</span>
            <span v-if="scope.row.name=='classavg'">班均分</span>
          </template>
        </el-table-column>
        <el-table-column
          min-width="120"
          :label="headData.subjectname" v-for="headData in tableDataHead" :key="headData.subjectid">
          <template slot-scope="scope">
            <span>{{scope.row[scope.row.name+headData.subjectid]}}</span>
          </template>
        </el-table-column>
        <el-table-column
          min-width="120"
          label="总分">
          <template slot-scope="scope">
            <span>{{scope.row[scope.row.name+'total']}}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-row type="flex" justify="center">
      <el-col :span="20">
        <h5>成绩对比图</h5>
        <el-row>
          <div id="progress1" style="width: 100%;height:400px;"></div>
          <div id="progress2" style="width: 100%;height:400px;"></div>
        </el-row>
        <el-row>
          <el-col :span="12">
            <h5>年级名次</h5>
            <div id="progress3" style="width: 100%;height:400px;"></div>
          </el-col>
          <el-col :span="12">
            <h5>班级名次</h5>
            <div id="progress4" style="width: 100%;height:400px;"></div>
          </el-col>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import echarts from 'echarts/lib/echarts'
  // 引入柱状图
  import 'echarts/lib/chart/radar'  //雷达图
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
        tableDataHead: [],
        userInfo: {},
        scoreInfo: {},
        selectParam: {
          examinationid: ''
        },
        tableData: [],
        chart1: '',
        chart2: '',
        chart3: '',
        chart4: '',
        option1: {
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'line'
            }
          },
          legend: {
            data: []
          },
          grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
          },
          xAxis: {
            type: 'value',
            splitLine: {
              show: false
            }
          },
          yAxis: {
            type: 'category',
            data: ['个人成绩', '平均分']
          },
          series: []
        },
        option2: {
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'line'
            }
          },
          grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
          },
          xAxis: {
            type: 'value',
            splitLine: {
              show: false
            }
          },
          yAxis: {
            type: 'category',
            data: ['个人成绩', '平均分']
          },
          series: []
        },
        option3: {
          tooltip: {},
          toolbox: {
            show: true,
            feature: {
              mark: {show: true},
              dataView: {show: true, readOnly: false},
              restore: {show: true},
              saveAsImage: {show: true}
            }
          },
          calculable: true,
          radar: {
            name: {
              textStyle: {
                color: '#fff',
                backgroundColor: '#999',
                borderRadius: 3,
                padding: [3, 5]
              }
            },
            indicator: []
          },
          series: [{
            type: 'radar',
            itemStyle: {
              normal: {
                areaStyle: {
                  type: 'default'
                },
                color: 'rgba(82,162,249,0.5)'
              }
            },
            data: [
              {
                value: [],
                name: '年级名次'
              }
            ]
          }]
        },
        option4: {
          tooltip: {},
          toolbox: {
            show: true,
            feature: {
              mark: {show: true},
              dataView: {show: true, readOnly: false},
              restore: {show: true},
              saveAsImage: {show: true}
            }
          },
          calculable: true,
          radar: {
            name: {
              textStyle: {
                color: '#fff',
                backgroundColor: '#999',
                borderRadius: 3,
                padding: [3, 5]
              }
            },
            indicator: []
          },
          series: [
            {
              type: 'radar',
              itemStyle: {
                normal: {
                  areaStyle: {
                    type: 'default'
                  },
                  color: 'rgba(82,162,249,0.5)'
                }
              },
              data: [
                {
                  value: [],
                  name: '班级名次'
                }
              ]
            }
          ]
        }
      }
    },
    mounted: function () {
      this.chart1 = echarts.init(document.getElementById('progress1'));
      this.chart2 = echarts.init(document.getElementById('progress2'));
      this.chart3 = echarts.init(document.getElementById('progress3'));
      this.chart4 = echarts.init(document.getElementById('progress4'));
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
        if (!this.selectParam.examinationid) {
          this.vmMsgWarning('请选择考试!');
          return false;
        }
        this.loadData(this.selectParam);
      },
      loadData(data){
        var self = this;
        req.ajaxSend('/school/Achievement/personal/type/scorequery', 'post', data, function (res) {
          let d = {
            name: '',
            type: 'bar',
            stack: '总分',
            barWidth: 50,
            label: {
              normal: {
                show: false,
                position: 'insideRight',
              }
            },
            itemStyle: {
              normal: {
                color: '#fdd465'
              }
            },
            data: []
          };
          self.scoreInfo = res;
          self.tableDataHead = res.subjectlist;
          self.tableData = [];
          self.option1.series = [];
          self.option2.series = [];
          self.option3.radar.indicator = [];
          self.option3.series[0].data[0].value = [];
          self.option4.radar.indicator = [];
          self.option4.series[0].data[0].value = [];
          for (let obj of res.subjectlist) {
            let d1 = {
              name: obj.subjectname,
              icon: 'circle'
            }, d2 = {
              name: obj.subjectname,
              type: 'bar',
              stack: '总分',
              barWidth: 50,
              label: {
                normal: {
                  show: false,
                  position: 'insideRight'
                }
              },
              data: []
            }, d3 = {
              text: obj.subjectname,
              max: res.gradeAll
            }, d4 = {
              text: obj.subjectname,
              max: res.classAll
            };
            d2.data.push(res.classscore[obj.subjectid]);
            d2.data.push(res.classavg[obj.subjectid]);
            self.option1.legend.data.push(d1);
            self.option1.series.push(d2);
            self.option3.radar.indicator.push(d3);
            self.option4.radar.indicator.push(d4);
            self.option3.series[0].data[0].value.push(res.gradeRanking[obj.subjectid]);
            self.option4.series[0].data[0].value.push(res.classRanking[obj.subjectid]);
          }
          d.data.push(res.classscore.total);
          d.data.push(res.classavg.total);
          self.option2.series.push(d);
          for (let name in res) {
            if (name != 'subjectlist' && name != 'gradeAll' && name != 'classAll') {
              let obj = {
                name: name
              };
              for (let mbj in res[name]) {
                obj[name + mbj] = res[name][mbj];
              }
              self.tableData.push(obj);
            }
          }
          self.chart1.setOption(self.option1);
          self.chart2.setOption(self.option2);
          self.chart3.setOption(self.option3);
          self.chart4.setOption(self.option4);
        })
      }
    }
  }
</script>
<style>
  .personalResultsQuery {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    font-size: 14px;
  }

  .personalResultsQuery h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .personalResultsQuery .scoreRow {
    margin: 1.125rem 0;
  }

  .personalResultsQuery .scoreRowOne {
    margin: 2rem 0 1.125rem;
  }

  .personalResultsQuery .el-table td, .personalResultsQuery .el-table th {
    text-align: center;
  }

  .personalResultsQuery .scoreQuery_btn .el-button {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }

  .personalResultsQuery .test {
    width: 15.625rem;
  }

  .personalResultsQuery .formInline .el-form-item {
    margin-right: 2.5rem;
  }

  .personalResultsQuery .l_gap {
    margin-left: 1rem;
  }

  .personalResultsQuery .personalResultsQuery_bread {
    padding: 0 1.25rem;
    font-size: 1.125rem;
    cursor: pointer;
  }

  .personalResultsQuery .personalResultsQuery_bread + .personalResultsQuery_bread {
    border-left: 2px solid #d2d2d2;
  }

  .personalResultsQuery .personalResultsQuery_bread.active {
    color: #4da1ff;
  }

  .personalResultsQuery .el-table th {
    background-color: #deeefe;
    height: 3.5rem;
  }

  .personalResultsQuery .el-table td {
    height: 3rem;
    font-size: .875rem;
  }

  .personalResultsQuery .el-table__footer-wrapper thead div, .personalResultsQuery .el-table__header-wrapper thead div, .personalResultsQuery .el-table__fixed-header-wrapper thead div {
    background-color: #deeefe;
  }

  .personalResultsQuery h5 {
    font-size: 1.5rem;
    text-align: center;
    margin: 3rem 0 1.5rem;
  }
</style>
