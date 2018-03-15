<template>
  <div class="proctorArrangementArts">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb">
        <span :class="{'breadcrumb_active':actIndex==idx}" v-for="(titleSubject,idx) in titleSubjectList"
              @click="changeData(idx)">{{titleSubject.branch}}</span>
      </span>
    </el-row>
    <el-row type="flex" align="middle" class="examManager_row">
      <el-col :span="18">
        <el-form :inline="true" class="formInline">
          <el-form-item label="监考安排规则：">
            <el-select v-model="generatedParam.mode" placeholder="请选择" class="proctor_rules">
              <el-option
                v-for="item in proctorRules"
                :key="item.value"
                :label="item.label"
                :value="item.label">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" class="select" @click="generated">生成监考安排</el-button>
          </el-form-item>
        </el-form>
      </el-col>
      <el-col :span="8">
        <el-row type="flex" justify="end">
          <el-button type="primary" class="select" @click="save">保存</el-button>
        </el-row>
      </el-col>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row class="alertsBtn">
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
        <el-button class="delete" title="打印" @click="operationData('out')">
          <img class="delete_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
               alt="">
          <img class="delete_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
               alt="">
        </el-button>
      </el-button-group>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        border
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          type="index"
          width="80"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="room"
          label="考场">
        </el-table-column>
        <el-table-column
          :label="tableHead.date" :key="idx" v-for="(tableHead,idx) in tableHeadData">
          <el-table-column
            :label="subject.subjectname" :key="ix" v-for="(subject,ix) in tableHead.subjectlist">
            <template slot-scope="scope">
              <div v-if="scope.row[subject.ensubjectid]&&scope.row[subject.ensubjectid].length!=0">
                <el-col :span="12" v-for="(teacher,i) in scope.row[subject.ensubjectid]" :key="teacher.id">
                  <span class="teacherName"
                        @click="exchangeTeacher(scope.$index,subject.ensubjectid,i)">{{teacher.name||'- -'}}</span>
                </el-col>
              </div>
            </template>
          </el-table-column>
        </el-table-column>
      </el-table>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        proctorRules: [{
          value: '0',
          label: '不限制'
        }, {
          value: '1',
          label: '本学科教师不要监考本学科考试'
        }, {
          value: '2',
          label: '本学科教师要监考本学科考试'
        }],
        actIndex: 0,
        titleSubjectList: [],
        tableData: [],
        tableHeadData: [],
        tableDataAll: [],
        generatedParam: {
          examinationid: '',
          mode: ''
        },
        saveParam: {
          examinationid: '',
          data: []
        },
        exchangeData: [],
        loading: false
      }
    },
    created: function () {
      var self = this, data = {
        examinationid: this.$route.params.examinationid
      };
      self.generatedParam.examinationid = data.examinationid;
      self.saveParam.examinationid = data.examinationid;
      self.loading = true;
      req.ajaxSend('/school/Examination/exmanagement/type/invigilatorarrange/typename/find', 'post', data, function (res) {
        self.tableHeadData = res.title[0].datelist;
        self.$nextTick(function () {
          self.tableData = res.data[0].roomlist;
        });
        self.titleSubjectList = res.title;
        self.tableDataAll = res.data;
        self.loading = false;
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      changeData(idx){   //下拉框切换数据
        this.actIndex = idx;
        this.tableHeadData = this.titleSubjectList[idx].datelist;
        this.$nextTick(function () {
          this.tableData = this.tableDataAll[idx].roomlist;
        });
      },
      generated(){
        var self = this;
        if (!self.generatedParam.mode) {
          self.vmMsgWarning('请选择监考安排规则!');
          return false;
        }
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/invigilatorarrange/typename/generate', 'post', self.generatedParam, function (res) {
          if (res.return) {
            self.tableHeadData = res.title[self.actIndex].datelist;
            self.$nextTick(function () {
              self.tableData = res.data[self.actIndex].roomlist;
            });
            self.titleSubjectList = res.title;
            self.tableDataAll = res.data;
          } else {
            self.vmMsgWarning(res.msg);
          }
          self.loading = false;
        });
      },
      exchangeTeacher(idx, name, ix){  //点击切换老师
        var chData = {
          row: idx,
          name: name,
          index: ix,
          userName: this.tableData[idx][name][ix].name,
          userId: this.tableData[idx][name][ix].userid,
          ensubjectid: this.tableData[idx][name][ix].ensubjectid
        };
        if (this.exchangeData.length != 0) {
          let state = 0, a = this.exchangeData[0].row, b = this.exchangeData[0].name, c = this.exchangeData[0].index,
            userName = this.exchangeData[0].userName, userId = this.exchangeData[0].userId, n = 0,
            objData1=this.tableData[a][b][c],
            objData2=this.tableData[idx][name][ix],
            ensubjectid1 = objData1.ensubjectid,
            ensubjectid2 = objData2.ensubjectid,
            isRoom1 = objData1.roomid == '巡考' || objData1.roomid == '总巡考',
            isRoom2 = objData2.roomid == '巡考' || objData2.roomid == '总巡考';
          if (objData1.userid == objData2.userid) {
            this.exchangeData = [];
            return false;
          }
          if (ensubjectid1 != ensubjectid2) {   //不同科目交换
            for (let obj of this.tableData) {
              if (obj.roomid != '巡考' && obj.roomid != '总巡考') {
                for (let mbj of obj[ensubjectid2]) {
                  if (mbj.userid && (mbj.userid == objData1.userid)) {
                    state++;
                  }
                }
                for (let mbj of obj[ensubjectid1]) {
                  if (mbj.userid && (mbj.userid == objData2.userid)) {
                    state++;
                  }
                }
              } else {
                if (isRoom1 && isRoom2) {
                  for (let mbj of obj[ensubjectid2]) {
                    if (mbj.userid && (mbj.userid == objData1.userid) && (mbj.roomid == objData2.roomid)) {
                      state++;
                    }
                  }
                  for (let mbj of obj[ensubjectid1]) {
                    if (mbj.userid && (mbj.userid == objData2.userid) && (mbj.roomid == objData1.roomid)) {
                      state++;
                    }
                  }
                } else {
                  if (!isRoom1 && isRoom2) {
                    for (let mbj of obj[ensubjectid2]) {
                      if (mbj.userid && (mbj.userid == objData1.userid) && (mbj.roomid == objData2.roomid)) {
                        state++;
                      }
                    }
                    for (let mbj of obj[ensubjectid1]) {
                      if (mbj.userid && (mbj.userid == objData2.userid)) {
                        state++;
                      }
                    }
                  }
                  if (isRoom1 && !isRoom2) {
                    for (let mbj of obj[ensubjectid1]) {
                      if (mbj.userid && (mbj.userid == objData2.userid) && (mbj.roomid == objData1.roomid)) {
                        state++;
                      }
                    }
                    for (let mbj of obj[ensubjectid2]) {
                      if (mbj.userid && (mbj.userid == objData1.userid)) {
                        state++;
                      }
                    }
                  }
                  if (!isRoom1 && !isRoom2) {
                    for (let mbj of obj[ensubjectid2]) {
                      if (mbj.userid && (mbj.userid == objData1.userid)) {
                        state++;
                      }
                    }
                    for (let mbj of obj[ensubjectid1]) {
                      if (mbj.userid && (mbj.userid == objData2.userid)) {
                        state++;
                      }
                    }
                  }
                }
              }
            }
          } else {   //同一科目内部交换
            for (let obj of this.tableData[a][ensubjectid1]) {
              if (obj.userid && (obj.userid == objData2.userid)) {
                state++;
              }
            }
            for (let obj of this.tableData[idx][ensubjectid2]) {
              if (obj.userid && (obj.userid == objData1.userid)) {
                state++;
              }
            }
            if (!isRoom1 && isRoom2) {
              for (let obj of this.tableData) {
                for (let mbj of obj[ensubjectid2]) {
                  if (mbj.userid && (mbj.userid == objData2.userid)) {
                    n++;
                  }
                }
              }
            }
            if (isRoom1 && !isRoom2) {
              for (let obj of this.tableData) {
                for (let mbj of obj[ensubjectid1]) {
                  if (mbj.userid && (mbj.userid == objData1.userid)) {
                    n++;
                  }
                }
              }
            }
            if (n >= 2) {
              state++;
            }
          }
          if (state != 0) {
            this.exchangeData = [];
            return false;
          }
          objData1.name = objData2.name;
          objData1.userid = objData2.userid;
          objData2.name = userName;
          objData2.userid = userId;
          this.exchangeData = [];
        } else {
          this.exchangeData.push(chData);
        }
      },
      operationData(type){
        var tablestr = '<table class="table table=bordered" style="margin-bottom:0"><thead>';
        let tableThTr = '<tr><th rowspan="2">考场</th>', tableThTr1 = '<tr>', tableTd = '';
        for (let obj of this.tableHeadData) {
          tableThTr += '<th colspan="' + obj.subjectlist.length + '">' + obj.date + '</th>';
          for (let mbj of obj.subjectlist) {
            tableThTr1 += '<th>' + mbj.subjectname + '</th>';
          }
        }
        for (let obj of this.tableData) {
          let tTr = '<tr>';
          tTr += '<td>' + obj.room + '</td>';
          for (let mbj of this.tableHeadData) {
            for (let nbj of mbj.subjectlist) {
              tTr += '<td>' + (obj[nbj.ensubjectid][0].name || '') + ' ' + (obj[nbj.ensubjectid][1].name || '') + '</td>'
            }
          }
          tTr += '</tr>';
          tableTd += tTr;
        }
        tableThTr += '</tr>';
        tableThTr1 += '</tr>';
        tablestr += tableThTr + tableThTr1 + '</thead><tbody>';
        tablestr += tableTd;
        tablestr += '</tbody></table>';
        if (type == 'out') {
          req.downloadFile('.proctorArrangementArts', '/school/Examination/exmanagement/type/invigilatorarrange/typename/export?examinationid=' + this.generatedParam.examinationid, 'post');
        } else if (type == 'copy') {
          if ($("#bstableCopy").length < 1) {
            var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
            $('.proctorArrangementArts').append(element);
          }
          $("#bstableCopy").html(tablestr);
          $("#bstableCopy").select(); // 选择对象
          document.execCommand("Copy");
          alert('复制成功,请粘贴到Excel表格中！');
        } else {
          var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
          var printStyle, printContent;
          printStyle = "<style>" + printcss() + printCssStr + "</style>";
          printContent = printStyle + "<body>" + tablestr + "</body>";

          var newWin = window.open("");//新打开一个空窗口
          newWin.document.write(printContent);//将表格添加进新的窗口
          newWin.document.close();//在IE浏览器中使用必须添加这一句
          newWin.focus();//在IE浏览器中使用必须添加这一句

          newWin.print();//打印
          newWin.close();//关闭窗口

          function printcss() {
            var css = ".table-bordered {border: 1px solid #000;}.table {width: 100%;max-width: 100%;margin-bottom: 40px;}.table {border-spacing: 0;border-collapse: collapse;}.table td{border: 1px solid #000;vertical-align: middle;text-align:center;font-size:15px;height:30px;}.table th{padding:6px; vertical-align: top; border: 1px solid #000;text-align:center;  font-weight:bold;  font-size:13px;}.table div{  text-align:center; } .table .w_35{width:35px; }.table .w_100{width:100px; }.editdiv{ width: 100%;  border: 0;  height: 30px;   font-size: 15px;  font-weight: normal;  text-align: center;  line-height:30px; }.ng-table-filters{display:none;};input{width: 100%;border: 0;height:30px;line-height:30px;font-size: 15px;font-weight: normal;text-align: center;}";
            return css;
          }
        }
      },
      save(){
        var self = this;
        self.saveParam.data = [];
        for (let nobj of self.titleSubjectList) {
          for (let obj of nobj.datelist) {
            for (let ob of obj.subjectlist) {
              for (let dom of self.tableDataAll) {
                for (let mbj of dom.roomlist) {
                  if (mbj[ob.ensubjectid]) {
                    for (let nbj of mbj[ob.ensubjectid]) {
                      let data = {
                        userid: nbj.userid,
                        roomid: nbj.roomid,
                        ensubjectid: ob.ensubjectid
                      };
                      self.saveParam.data.push(data);
                    }
                  }
                }
              }
            }
          }
        }
        req.ajaxSend('/school/Examination/exmanagement/type/invigilatorarrange/typename/save', 'post', self.saveParam, function (res) {
          if (res.return) {
            self.vmMsgSuccess('保存成功!');
            var toData = {
              examinationid: self.$route.params.examinationid
            };
            self.loading = true;
            req.ajaxSend('/school/Examination/exmanagement/type/invigilatorarrange/typename/find', 'post', toData, function (res) {
              self.titleSubjectList = res.title;
              self.tableDataAll = res.data;
              self.tableData = res.data[self.actIndex].roomlist;
              self.tableHeadData = res.title[self.actIndex].datelist;
              self.loading = false;
            })
          } else {
            self.vmMsgError(res.msg);
          }
        });
      }
    }
  }
</script>
<style>
  .proctorArrangementArts .el-button.select {
    padding: 10px 25px;
  }

  .proctorArrangementArts .proctor_rules {
    width: 16rem;
  }

  .proctorArrangementArts .el-table--border td {
    border-right: none;
  }

  .proctorArrangementArts .teacherName {
    display: inline-block;
    width: 100%;
    cursor: pointer;
  }

  .proctorArrangementArts .teacherName.active {
    color: #4da1ff;
  }
</style>
