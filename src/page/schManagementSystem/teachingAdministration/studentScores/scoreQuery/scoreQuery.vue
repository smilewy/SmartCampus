<template>
  <div class="scoreQuery">
    <h3>成绩查询</h3>
    <el-row class="scoreRow scoreRowOne">
      <el-form :inline="true" class="formInline">
        <el-form-item label="年级：">
          <el-select v-model="gradeid" placeholder="请选择" class="grade" @change="selectExam">
            <el-option
              v-for="item in gradeList"
              :key="item.gradeid"
              :label="item.name"
              :value="item.gradeid">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="考试：">
          <el-select v-model="selectParam.examinationid" placeholder="请选择" class="test" @change="selectBranch">
            <el-option
              v-for="item in examList"
              :key="item.examinationid"
              :label="item.examination"
              :value="item.examinationid">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="科类：">
          <el-select v-model="selectParam.branchid" placeholder="请选择" class="subject" @change="selectClass">
            <el-option
              v-for="item in branchList"
              :key="item.branchid"
              :label="item.branch"
              :value="item.branchid">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="班级：">
          <el-select multiple v-model="selectParam.classid" placeholder="请选择" class="l_class">
            <el-option
              v-for="item in classList"
              :key="item.clsssid"
              :label="item.clsssname"
              :value="item.clsssid">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="显示单科年级排名：">
          <el-switch
            v-model="showSingleRank"
            active-text="是"
            inactive-text="否"
            active-color="#09baa7"
            inactive-color="#ff4949"
            @change="isShowSingleRank">
          </el-switch>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="scoreRow">
      <el-form :inline="true" class="formInline">
        <el-form-item label="专业：">
          <el-select v-model="major" placeholder="请选择" class="grade">
            <el-option label="全部" value="全部"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="总分统计科目：">
          <el-checkbox-group v-model="subjectCheckList">
            <el-checkbox :label="subject.subjectname" v-for="subject in subjectList"
                         :key="subject.subjectid"></el-checkbox>
          </el-checkbox-group>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row type="flex" align="middle" class="scoreRow">
      <el-col :span="18">
        <el-button-group class="rank_btn">
          <el-button :class="{'active':rankIndex==0}" @click="changeRank(0)"><span>全部</span></el-button>
          <el-button :class="{'active':rankIndex==1}" @click="changeRank(1)"><span>前</span> <span v-show="rankIndex!=1">X</span><input
            type="text" v-model="selectParam.afterNum" class="topSeveral" v-show="rankIndex==1">
            <span>名</span></el-button>
          <el-button :class="{'active':rankIndex==2}" @click="changeRank(2)"><span>后</span> <span v-show="rankIndex!=2">X</span><input
            type="text" v-model="selectParam.beforNum" class="topSeveral" v-show="rankIndex==2">
            <span>名</span></el-button>
        </el-button-group>
        <el-checkbox class="fillLeft" v-model="isRemoveMarkZero">除去总分0分</el-checkbox>
        <el-checkbox v-model="isRemoveResultZero">除去单科缺考</el-checkbox>
      </el-col>
      <el-col :span="6" class="scoreQuery_btn">
        <el-button type="primary" @click="search">
          <img src="../../../../../assets/img/schManagementSystem/teachingAdministration/studentScores/icon_search.png"
               alt="">
          <span>查询</span>
        </el-button>
      </el-col>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button class="delete" title="导出" @click="operationData('out')">
          <img class="delete_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
               alt="">
          <img class="delete_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
               alt="">
        </el-button>
        <el-button-group class="secBtn-group">
          <el-button class="filt" title="复制" @click="operationData('copy')">
            <img class="filt_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                 alt="">
            <img class="filt_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="打印" @click="operationData('print')">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                 alt="">
          </el-button>
        </el-button-group>
      </el-col>
      <el-col :span="6">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入关键字"
            suffix-icon="el-icon-search"
            v-model="selectParam.find"
            @change="goSearch">
          </el-input>
        </div>
      </el-col>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        @sort-change="sort"
        @row-click="toPersonalScore"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="name"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="className"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="serialNumber"
          label="座号" sortable="custom">
        </el-table-column>
        <el-table-column
          :label="subject.subjectname" v-for="(subject,idx) in tableHeadData" :key="idx">
          <template slot-scope="scope">
            <span>{{scope.row[subject.subjectid].results}}</span> <span v-if="showSingleRank">| {{scope.row[subject.subjectid].ranking}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="totalScore"
          label="总分" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="classRanking"
          label="班名次" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="gradeRanking"
          label="级名次" sortable="custom">
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData&&tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="selectParam.page"
        :page-size="selectParam.limit"
        layout="prev, pager, next, jumper"
        :total="totalNum">
      </el-pagination>
    </el-row>
    <el-dialog
      title="个人成绩"
      :modal="false"
      :visible.sync="dialogVisible"
      width="70%"
      :before-close="handleClose">
      <el-row class="dialogContent">
        <el-row type="flex" justify="center">
          <span class="scoreQuery_bread" :class="{'active':scoreQueryType==0}" @click="changeScore(0)">成绩查询</span>
          <span class="scoreQuery_bread" :class="{'active':scoreQueryType==1}" @click="changeScore(1)">成绩对比</span>
        </el-row>
        <el-row class="scoreRowOne" v-show="scoreQueryType==0">
          <el-row>
            <el-table
              :data="tableDataScore"
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
                :label="headData.subjectname" v-for="headData in tableDataScoreHead" :key="headData.subjectid">
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
        </el-row>
        <el-row class="scoreRowOne" v-show="scoreQueryType==1">
          <el-row>
            <el-form :inline="true" class="formInline">
              <el-form-item label="考试：">
                <el-select v-model="selectParam2.examinationid1" placeholder="请选择" class="test">
                  <el-option
                    v-for="item in examListScore"
                    :key="item.examinationid"
                    :label="item.examination"
                    :value="item.examinationid">
                  </el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="对比考试：">
                <el-select v-model="selectParam2.examinationid2" placeholder="请选择" class="test">
                  <el-option
                    v-for="item in examListScore"
                    :key="item.examinationid"
                    :label="item.examination"
                    :value="item.examinationid">
                  </el-option>
                </el-select>
              </el-form-item>
              <el-form-item class="scoreQuery_btns">
                <el-button type="primary" @click="searchContrast">
                  <img
                    src="../../../../../assets/img/schManagementSystem/teachingAdministration/studentScores/icon_search.png"
                    alt="">
                  <span>查询</span>
                </el-button>
              </el-form-item>
            </el-form>
          </el-row>
          <el-row type="flex" justify="center">
            <el-col :span="20">
              <h5>成绩对比图</h5>
              <div id="progress5" style="width: 100%;height:400px;"></div>
            </el-col>
          </el-row>
        </el-row>
      </el-row>
      </span>
    </el-dialog>
  </div>
</template>
<script>
  import echarts from 'echarts/lib/echarts'
  // 引入柱状图
  import 'echarts/lib/chart/radar'  //雷达图
  import 'echarts/lib/chart/bar'  //柱状图
  import 'echarts/lib/chart/line'  //线
  // 引入提示框和标题组件
  import 'echarts/lib/component/tooltip'
  import 'echarts/lib/component/title'
  import 'echarts/lib/component/legendScroll'
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        gradeList: [],
        examList: [],
        branchList: [],
        classList: [],
        subjectList: [],
        tableData: [],
        tableHeadData: [],
        totalNum: 0,
        rankIndex: 0,
        isRemoveMarkZero: false,
        isRemoveResultZero: false,
        subjectCheckList: [],
        showSingleRank: false,
        major: '全部',
        gradeid: '',
        selectParam: {
          page: 1,
          limit: 50,
          field: '',
          find: '',
          order: '',
          examinationid: '',
          branchid: '',
          classid: [],
          subjectid: [],
          afterNum: '',
          beforNum: '',
          isRemoveMarkZero: '',
          isRemoveResultZero: '',
        },
        loading: false,
        dialogVisible: false,
        //个人成绩
        scoreQueryType: 0,
        scoreQuery: {
          userid: ''
        },
        selectParam2: {
          examinationid1: '',
          examinationid2: ''
        },
        examListScore: [],
        tableDataScoreHead: [],
        tableDataScore: [],
        chart1: '',
        chart2: '',
        chart3: '',
        chart4: '',
        chart5: '',
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
        },
        option5: {
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
    created: function () {
      var self = this, data;
      req.ajaxSend('/school/Achievement/achievementFind/type/findgrade', 'post', '', function (res) {
        self.gradeList = res;
        self.gradeid = res[0].gradeid;
        data = {
          gradeid: self.gradeid
        };
        req.ajaxSend('/school/Achievement/achievementFind/type/findexam', 'post', data, function (res) {
          self.examList = res;
          if (res.length != 0) {
            self.selectParam.examinationid = res[0].examinationid;
            self.selectBranch();
          }
        })
      })
    },
    methods: {
      selectExam(){
        var self = this, data = {
          gradeid: self.gradeid
        };
        self.selectParam.examinationid = '';
        req.ajaxSend('/school/Achievement/achievementFind/type/findexam', 'post', data, function (res) {
          self.examList = res;
          if (res.length != 0) {
            self.selectParam.examinationid = res[0].examinationid;
            self.selectBranch();
          }
          self.selectParam.branchid = '';
          self.branchList = [];
          self.selectParam.classid = [];
          self.classList = [];
          self.subjectList = [];
        })
      },
      selectBranch(){
        var self = this, data = {
          examinationid: self.selectParam.examinationid
        };
        self.selectParam.branchid = '';
        req.ajaxSend('/school/Achievement/achievementFind/type/findclass', 'post', data, function (res) {
          self.branchList = res;
          self.selectParam.classid = [];
          self.classList = [];
          self.subjectList = [];
        })
      },
      selectClass(){
        var self = this, data = {
          examinationid: self.selectParam.examinationid,
          branchid: self.selectParam.branchid
        };
        self.classList = [];
        for (let obj of self.branchList) {
          if (obj.branchid == self.selectParam.branchid) {
            self.selectParam.classid = [];
            self.classList = obj.classlist;
            break;
          }
        }
        req.ajaxSend('/school/Achievement/achievementFind/type/subjectfind', 'post', data, function (res) {
          self.subjectList = res;
          for (let obj of res) {
            self.subjectCheckList.push(obj.subjectname);
          }
        })
      },
      changeRank(type){
        this.rankIndex = type;
        if (type == 0) {   //全部
          this.selectParam.afterNum = '';
          this.selectParam.beforNum = '';
        } else if (type == 1) {  //前几名
          this.selectParam.beforNum = '';
        } else {  //后几名
          this.selectParam.afterNum = '';
        }
      },
      isShowSingleRank(state){
        this.showSingleRank = state;
      },
      search(){
        this.selectParam.page = 1;
        this.selectParam.find = '';
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.selectParam.isRemoveMarkZero = this.isRemoveMarkZero ? 1 : 0;
        this.selectParam.isRemoveResultZero = this.isRemoveResultZero ? 1 : 0;
        if (!this.selectParam.branchid) {
          this.vmMsgWarning('请选择科类!');
          return false;
        }
        if (this.selectParam.classid.length == 0) {
          this.vmMsgWarning('请选择班级!');
          return false;
        }
        this.selectParam.subjectid = [];
        for (let obj of this.subjectCheckList) {
          for (let mbj of this.subjectList) {
            if (obj == mbj.subjectname) {
              this.selectParam.subjectid.push(mbj.subjectid);
            }
          }
        }
        this.loadData(this.selectParam);
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      toPersonalScore(row, event, column){
        var self = this, data = {
          userid: row.userid
        }, data1 = {
          userid: row.userid,
          examinationid: self.selectParam.examinationid
        };
        self.dialogVisible = true;
        self.scoreQuery.userid = row.userid;
        self.selectParam2.examinationid1 = '';
        self.selectParam2.examinationid2 = '';
        self.examListScore = [];
        self.scoreQueryType = 0;
        if (self.chart5) {
          self.chart5.clear();
        }
        req.ajaxSend('/school/Achievement/achievementFind/type/personalfind', 'post', data1, function (res) {
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
          if (!self.chart1) {
            self.chart1 = echarts.init(document.getElementById('progress1'));
            self.chart2 = echarts.init(document.getElementById('progress2'));
            self.chart3 = echarts.init(document.getElementById('progress3'));
            self.chart4 = echarts.init(document.getElementById('progress4'));
          } else {
            self.chart1.clear();
            self.chart2.clear();
            self.chart3.clear();
            self.chart4.clear();
          }
          self.tableDataScoreHead = res.subjectlist;
          self.tableDataScore = [];
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
              self.tableDataScore.push(obj);
            }
          }
          self.chart1.setOption(self.option1);
          self.chart2.setOption(self.option2);
          self.chart3.setOption(self.option3);
          self.chart4.setOption(self.option4);
        });
        req.ajaxSend('/school/Achievement/achievementFind/type/userExam', 'post', data, function (res) {
          self.examListScore = res;
        });
      },
      changeScore(idx){
        this.scoreQueryType = idx;
      },
      searchContrast(){
        var self = this, exId = {
          userid: self.scoreQuery.userid,
          examinationid: []
        };
        if (!self.selectParam2.examinationid1 || !self.selectParam2.examinationid2) {
          self.vmMsgWarning('请选择考试！');
          return false;
        }
        if (self.selectParam2.examinationid1 == self.selectParam2.examinationid2) {
          self.vmMsgWarning('对比考试不能是同一场考试！');
          return false;
        }
        exId.examinationid.push(this.selectParam2.examinationid1);
        exId.examinationid.push(this.selectParam2.examinationid2);
        if (!self.chart5) {
          self.chart5 = echarts.init(document.getElementById('progress5'));
        }
        req.ajaxSend('/school/Achievement/achievementFind/type/comparison', 'post', exId, function (res) {
          let d3 = {
            name: '班级名次',
            type: 'line',
            data: []
          }, d4 = {
            name: '年级名次',
            type: 'line',
            data: []
          };
          self.option5.xAxis[0].data = [];
          self.option5.legend.data = [];
          self.option5.series = [];
          self.option5.yAxis[1].max = res.classAll;
          self.option5.yAxis[2].max = res.gradeAll;
          for (let obj of res.data) {
            self.option5.xAxis[0].data.push(obj.examination);
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
            self.option5.series.push(d2);
            self.option5.legend.data.push(d1);
          }
          for (let obj of res.data) {
            d3.data.push(obj.value.classRanking);
            d4.data.push(obj.value.gradeRanking);
          }
          self.option5.series.push(d3);
          self.option5.series.push(d4);
          self.option5.legend.data.push({
            name: '班级名次'
          });
          self.option5.legend.data.push({
            name: '年级名次'
          });
          self.chart5.setOption(self.option5);
        })
      },
      handleClose(done) {
        done();
      },
      operationData(type){
        var url = '', rank = '';
        let sAy = [], hdData = {
          name: '姓名',
          className: '班级',
          serialNumber: '座号'
        };
        for (let obj of this.tableHeadData) {
          hdData[obj.subjectid] = obj.subjectname;
        }
        hdData.totalScore = '总分';
        hdData.classRanking = '班名次';
        hdData.gradeRanking = '级名次';
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name != 'name' && name != 'className' && name != 'serialNumber' && name != 'totalScore' && name != 'classRanking' && name != 'gradeRanking') {
              d[name] = obj[name].results || '';
            } else {
              d[name] = obj[name] || '';
            }
          }
          sAy.push(d)
        }
        if (type == 'out') {
          this.selectParam.isRemoveMarkZero = this.isRemoveMarkZero ? 1 : 0;
          this.selectParam.isRemoveResultZero = this.isRemoveResultZero ? 1 : 0;
          rank = this.showSingleRank ? 1 : 0;
          url = '?examinationid=' + this.selectParam.examinationid + '&branchid=' + this.selectParam.branchid + '&classid=' + this.selectParam.classid + '&subjectid=' + this.selectParam.subjectid + '&afterNum=' + this.selectParam.afterNum + '&beforNum=' + this.selectParam.beforNum + '&isRemoveMarkZero=' + this.selectParam.isRemoveMarkZero + '&isRemoveResultZero=' + this.selectParam.isRemoveResultZero + '&ranking=' + rank;
          req.downloadFile('.scoreQuery', '/school/Achievement/achievementFind/type/scoreexport' + url, 'post');
        } else if (type == 'copy') {
          req.copyTableData('.scoreQuery', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Achievement/achievementFind/type/acfind', 'post', data, function (res) {
          self.tableData = res.student;
          self.tableHeadData = res.subject;
          self.totalNum = res.pageclass.count;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .scoreQuery {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    font-size: 14px;
  }

  .scoreQuery h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .scoreQuery .scoreRow {
    margin: 1.125rem 0;
  }

  .scoreQuery .scoreRowOne {
    margin: 2rem 0 1.125rem;
  }

  .scoreQuery .el-table td, .scoreQuery .el-table th {
    text-align: center;
  }

  .scoreQuery .g-fuzzyInput {
    float: right;
  }

  .scoreQuery .d_line {
    margin-top: 1.125rem;
  }

  .scoreQuery .alertsBtn {
    margin: 1.125rem 0;
  }

  .scoreQuery .scoreQuery_btn {
    text-align: right;
    margin: 1.25rem 0;
  }

  .scoreQuery .scoreQuery_btn .el-button {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }

  .scoreQuery .grade {
    width: 8.75rem;
  }

  .scoreQuery .l_class {
    width: 9.375rem;
  }

  .scoreQuery .subject {
    width: 6.25rem;
  }

  .scoreQuery .test {
    width: 10.625rem;
  }

  .scoreQuery .fillLeft {
    margin-left: 2.5rem;
  }

  .scoreQuery .formInline .el-form-item {
    margin-right: 2.5rem;
    margin-bottom: 0;
  }

  .scoreQuery .el-checkbox-group {
    display: inline-block;
  }

  .scoreQuery .scoreRow .el-button-group .el-button.active {
    background-color: #09baa7;
    color: #fff;
    border: 1px solid #09baa7;
  }

  .scoreQuery .rank_btn .el-button {
    padding: 0;
    height: 30px;
    width: 6rem;
  }

  .scoreQuery input.topSeveral {
    width: 1.5rem;
    outline: none;
    border-top: none;
    border-left: none;
    border-right: none;
    border-bottom: 1px solid #1f2d3d;
    text-align: center;
  }

  .scoreQuery .scoreQuery_bread {
    padding: 0 1.25rem;
    font-size: 1.125rem;
    cursor: pointer;
  }

  .scoreQuery .scoreQuery_bread + .scoreQuery_bread {
    border-left: 2px solid #d2d2d2;
  }

  .scoreQuery .scoreQuery_bread.active {
    color: #4da1ff;
  }

  .scoreQuery .dialogContent {
    max-height: 40rem;
    overflow: auto;
  }

  .scoreQuery .dialogContent .el-table th {
    background-color: #deeefe;
    height: 3.5rem;
  }

  .scoreQuery .dialogContent .el-table td {
    height: 3rem;
    font-size: .875rem;
  }

  .scoreQuery .dialogContent .el-table__footer-wrapper thead div, .scoreQuery .dialogContent .el-table__header-wrapper thead div, .scoreQuery .dialogContent .el-table__fixed-header-wrapper thead div {
    background-color: #deeefe;
  }

  .scoreQuery .dialogContent h5 {
    font-size: 1rem;
    text-align: center;
    margin: 3rem 0 1.5rem;
  }

  .scoreQuery .scoreQuery_btns .el-button {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }
</style>
