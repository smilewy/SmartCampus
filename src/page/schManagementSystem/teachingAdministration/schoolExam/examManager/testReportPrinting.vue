<template>
  <div class="testReportPrinting">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>考务报表打印</h3>
    </el-row>
    <el-row class="examManager_row">
      <span>报表类型：</span>
      <el-select @change="changeData" v-model="reportType" placeholder="请选择" class="testNumber">
        <el-option
          v-for="item in reportTypes"
          :key="item.value"
          :label="item.label"
          :value="item.label">
        </el-option>
      </el-select>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button-group>
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
    </el-row>
    <el-row>
      <h2 v-if="reportType">{{reportTypeName}} - {{reportType}}</h2>
      <el-row class="dataList" v-loading="loading"
              element-loading-text="拼命加载中">
        <el-table
          :data="tableData"
          style="width: 100%"
          border
          v-show="reportType=='考场安排情况'"
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
            prop="seats"
            label="人数">
          </el-table-column>
          <el-table-column
            prop="columns"
            label="列数">
          </el-table-column>
          <el-table-column
            label="备注">
          </el-table-column>
        </el-table>
        <el-row v-show="reportType=='考场布局表'">
          <el-row v-for="(secRow,index) in tableData" :key="index">
            <el-row class="sec_head">{{secRow.room}}</el-row>
            <el-row class="secTable">
              <el-row>
                <el-col :span="5" class="resetThead">考场名</el-col>
                <el-col :span="3" class="resetThead">行数</el-col>
                <el-col :span="3" class="resetThead">列数</el-col>
                <el-col :span="3" class="resetThead">考场座号</el-col>
                <el-col :span="5" class="resetThead">姓名</el-col>
                <el-col :span="5" class="resetThead">考号</el-col>
              </el-row>
              <el-row v-for="(secData,idx) in secRow.seat" :key="idx">
                <el-col :span="5" class="resetTd">{{secData.roomName}}</el-col>
                <el-col :span="3" class="resetTd">{{secData.seatRow}}</el-col>
                <el-col :span="3" class="resetTd">{{secData.seatCol}}</el-col>
                <el-col :span="3" class="resetTd">{{secData.seatNumber}}</el-col>
                <el-col :span="5" class="resetTd">{{secData.name}}</el-col>
                <el-col :span="5" class="resetTd">{{secData.number}}</el-col>
              </el-row>
            </el-row>
          </el-row>
        </el-row>
        <el-row v-show="reportType=='座签表'">
          <el-row v-for="(secRow,index) in tableData" :key="index">
            <el-row class="sec_head">{{secRow.room}}</el-row>
            <el-row class="secTable">
              <el-row>
                <el-col :span="6" class="resetThead">考场</el-col>
                <el-col :span="4" class="resetThead">座号</el-col>
                <el-col :span="4" class="resetThead">姓名</el-col>
                <el-col :span="6" class="resetThead">考号</el-col>
                <el-col :span="4" class="resetThead">签名</el-col>
              </el-row>
              <el-row v-for="(secData,idx) in secRow.seat" :key="idx">
                <el-col :span="6" class="resetTd">{{secData.roomName}}</el-col>
                <el-col :span="4" class="resetTd">{{secData.seatNumber}}</el-col>
                <el-col :span="4" class="resetTd">{{secData.name}}</el-col>
                <el-col :span="6" class="resetTd">{{secData.number}}</el-col>
                <el-col :span="4" class="resetTd"></el-col>
              </el-row>
            </el-row>
          </el-row>
        </el-row>
        <el-row v-show="reportType=='分班考场安排表'">
          <el-row v-for="(secRow,index) in tableData" :key="index">
            <el-row class="sec_head">{{secRow.className}}</el-row>
            <el-row class="secTable">
              <el-row>
                <el-col :span="6" class="resetThead">序号</el-col>
                <el-col :span="6" class="resetThead">姓名</el-col>
                <el-col :span="6" class="resetThead">考号</el-col>
                <el-col :span="6" class="resetThead">考场名</el-col>
              </el-row>
              <el-row v-for="(secData,idx) in secRow.data" :key="idx">
                <el-col :span="6" class="resetTd">{{secData.key}}</el-col>
                <el-col :span="6" class="resetTd">{{secData.name}}</el-col>
                <el-col :span="6" class="resetTd">{{secData.number}}</el-col>
                <el-col :span="6" class="resetTd">{{secData.room}}</el-col>
              </el-row>
            </el-row>
          </el-row>
        </el-row>
        <el-row v-show="reportType=='考试安排表'">
          <el-row v-for="(secRow,index) in tableData" :key="index">
            <el-row class="sec_head">{{secRow.room}}</el-row>
            <el-row class="secTable">
              <el-row>
                <el-col :span="4" class="resetThead">考场</el-col>
                <el-col :span="4" class="resetThead">班级</el-col>
                <el-col :span="4" class="resetThead">班级座号</el-col>
                <el-col :span="4" class="resetThead">考场座号</el-col>
                <el-col :span="4" class="resetThead">姓名</el-col>
                <el-col :span="4" class="resetThead">考号</el-col>
              </el-row>
              <el-row v-for="(secData,idx) in secRow.value" :key="idx">
                <el-col :span="4" class="resetTd">{{secData.roomName}}</el-col>
                <el-col :span="4" class="resetTd">{{secData.className}}</el-col>
                <el-col :span="4" class="resetTd">{{secData.serialNumber}}</el-col>
                <el-col :span="4" class="resetTd">{{secData.seatnumber}}</el-col>
                <el-col :span="4" class="resetTd">{{secData.name}}</el-col>
                <el-col :span="4" class="resetTd">{{secData.number}}</el-col>
              </el-row>
            </el-row>
          </el-row>
        </el-row>
        <el-row v-show="reportType=='考场台卡'">
          <el-row type="flex" justify="center" v-for="(secRow,index) in tableData" :key="index">
            <el-col :span="10" class="taiCard">
              <p>{{secRow.name}}</p>
              <p>{{secRow.room}}</p>
              <p>座位数：{{secRow.seats}}</p>
            </el-col>
          </el-row>
        </el-row>
        <el-row v-show="reportType=='考签到表'">
          <el-row v-for="(secRow,index) in tableData" :key="index">
            <el-row class="sec_head">
              <span>{{secRow.bsname}}</span>
              <span>考场：{{secRow.room}}</span>
              <span>监考老师：{{secRow.teacher}}</span>
            </el-row>
            <el-table
              :data="secRow.list"
              style="width: 100%"
              border
            >
              <el-table-column
                prop="serialNumber"
                label="座位号">
              </el-table-column>
              <el-table-column
                prop="name"
                label="姓名">
              </el-table-column>
              <el-table-column
                label="签名">
              </el-table-column>
            </el-table>
          </el-row>
        </el-row>
        <el-table
          :data="tableData"
          style="width: 100%"
          border
          v-show="reportType=='监考任务安排统计表'"
        >
          <el-table-column
            type="index"
            width="80"
            label="序号">
          </el-table-column>
          <el-table-column
            prop="invigilator"
            label="监考">
          </el-table-column>
          <el-table-column
            prop="visits"
            label="巡考">
          </el-table-column>
          <el-table-column
            prop="totalinspection"
            label="总巡考">
          </el-table-column>
          <el-table-column
            prop="all"
            label="安排次数">
          </el-table-column>
          <el-table-column
            prop="time"
            label="总时长">
          </el-table-column>
          <el-table-column
            prop="timeaverage"
            label="均时长">
          </el-table-column>
          <el-table-column
            prop="name"
            label="教师">
          </el-table-column>
        </el-table>
        <el-table
          :data="tableData"
          style="width: 100%"
          border
          v-show="reportType=='考务会签到表'"
        >
          <el-table-column
            type="index"
            width="80"
            label="序号">
          </el-table-column>
          <el-table-column
            prop="name"
            label="姓名">
          </el-table-column>
          <el-table-column
            prop="phone"
            label="电话">
          </el-table-column>
        </el-table>
        <el-table
          :data="tableData"
          style="width: 100%"
          border
          v-show="reportType=='上报学生名单'||reportType=='不上报学生名单'"
        >
          <el-table-column
            prop="name"
            label="姓名">
          </el-table-column>
          <el-table-column
            prop="number"
            label="考号">
          </el-table-column>
          <el-table-column
            prop="sex"
            label="性别">
          </el-table-column>
          <el-table-column
            prop="branch"
            label="科类">
          </el-table-column>
          <el-table-column
            prop="scool"
            label="学校">
          </el-table-column>
          <el-table-column
            prop="hklx"
            min-width="150"
            label="户籍类别">
          </el-table-column>
          <el-table-column
            prop="tesults"
            label="总分">
          </el-table-column>
        </el-table>
        <el-table
          :data="tableData"
          style="width: 100%"
          border
          v-show="reportType=='不参考学生名单'"
        >
          <el-table-column
            type="index"
            width="80"
            label="序号">
          </el-table-column>
          <el-table-column
            prop="name"
            label="姓名">
          </el-table-column>
          <el-table-column
            prop="className"
            label="班级">
          </el-table-column>
        </el-table>
        <el-table
          :data="tableData"
          style="width: 100%"
          border
          v-show="reportType=='监考员座位卡'"
        >
          <el-table-column
            type="index"
            width="80"
            label="序号">
          </el-table-column>
          <el-table-column
            prop="name"
            label="姓名">
          </el-table-column>
        </el-table>
        <el-table
          :data="tableData"
          style="width: 100%"
          border
          v-show="reportType=='参考学生总名册'"
        >
          <el-table-column
            prop="name"
            label="姓名">
          </el-table-column>
          <el-table-column
            prop="grade"
            label="年级">
          </el-table-column>
          <el-table-column
            prop="className"
            label="班级">
          </el-table-column>
          <el-table-column
            prop="room"
            label="考场">
          </el-table-column>
          <el-table-column
            prop="seatnumber"
            label="座位号">
          </el-table-column>
          <el-table-column
            prop="idCard"
            label="身份证">
          </el-table-column>
          <el-table-column
            prop="scName"
            min-width="120"
            label="学校">
          </el-table-column>
          <el-table-column
            prop="number"
            label="考号">
          </el-table-column>
          <el-table-column label="考试科目">
            <el-table-column
              label="科目"
              min-width="120">
              <template slot-scope="scope">
                <el-row v-for="(subject,ix) in scope.row.subjectlist" :key="ix">{{subject.subject}}</el-row>
              </template>
            </el-table-column>
            <el-table-column
              label="老师"
              min-width="120">
              <template slot-scope="scope">
                <el-row v-for="(subject,ix) in scope.row.subjectlist" :key="ix">{{subject.teacher}}</el-row>
              </template>
            </el-table-column>
          </el-table-column>
        </el-table>
      </el-row>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        reportTypes: [{
          value: 0,
          label: '考场安排情况'
        }, {
          value: 1,
          label: '考场布局表'
        }, {
          value: 2,
          label: '座签表'
        }, {
          value: 3,
          label: '分班考场安排表'
        }, {
          value: 4,
          label: '考试安排表'
        }, {
          value: 5,
          label: '考场台卡'
        }, {
          value: 6,
          label: '考签到表'
        }, {
          value: 7,
          label: '监考任务安排统计表'
        }, {
          value: 8,
          label: '考务会签到表'
        }, {
          value: 9,
          label: '上报学生名单'
        }, {
          value: 10,
          label: '不上报学生名单'
        }, {
          value: 11,
          label: '不参考学生名单'
        }, {
          value: 12,
          label: '监考员座位卡'
        }, {
          value: 13,
          label: '参考学生总名册'
        }],
        selectParam: {},
        reportType: '',
        reportTypeName: '',
        tableData: [],
        loading: false
      }
    },
    methods: {
      returnFlowchart() {
        this.$router.push('/examManagerHome');
      },
      changeData() {
        var path = '/school/Examination/exmanagement/type/report/typename/', exId = this.$route.params.examinationid,
          gId = this.$route.params.gradeid,
          self = this;
        self.selectParam = {
          examinationid: exId
        };
        switch (self.reportType) {
          case '考场安排情况':
            path = path + 'arrange';
            break;
          case '考场布局表':
          case '座签表':
            path = path + 'roomseat';
            self.selectParam.gradeid = gId;
            self.selectParam.value = self.reportType == '考场布局表' ? 'roomseat' : 'seat';
            break;
          case '分班考场安排表':
          case '考试安排表':
            path = path + 'classroom';
            self.selectParam.gradeid = gId;
            self.selectParam.value = self.reportType == '分班考场安排表' ? 'class' : 'room';
            break;
          case '考场台卡':
            path = path + 'roomcard';
            break;
          case '考签到表':
            path = path + 'invigilatorsub';
            break;
          case '监考任务安排统计表':
            path = path + 'jiankaotongji';
            break;
          case '考务会签到表':
            path = path + 'kaowu';
            break;
          case '上报学生名单':
          case '不上报学生名单':
            path = path + 'xueshengmingdan';
            self.selectParam.reported = self.reportType == '上报学生名单' ? '是' : '否';
            break;
          case '不参考学生名单':
            path = path + 'bucankaoxuesheng';
            break;
          case '监考员座位卡':
            path = path + 'jiankaozuoweika';
            break;
          case '参考学生总名册':
            path = path + 'zongmingce';
            self.selectParam.gradeid = gId;
            break;
        }
        self.loading = true;
        req.ajaxSend(path, 'post', self.selectParam, function (res) {
          let nAry = [];
          self.loading = false;
          if (self.reportType == '参考学生总名册') {
            for (let obj of res.data) {
              for (let ob of obj.students) {
                ob.subjectlist = obj.subjectlist;
                nAry.push(ob);
              }
            }
            self.tableData = nAry;
          } else {
            self.tableData = res.data || res.room;
          }
          self.reportTypeName = res.examname;
        })
      },
      operationData(type){
        let sAy = [], hdData;
        switch (this.reportType) {
          case '考场安排情况':
            hdData = {
              room: '考场',
              seats: '人数',
              columns: '列数',
              remark: '备注'
            };
            sAy.push(hdData);
            for (let obj of this.tableData) {
              let d = {};
              for (let name in hdData) {
                if (name == 'seats' || name == 'columns') {
                  d[name] = obj[name];
                } else {
                  d[name] = obj[name] || '';
                }
              }
              sAy.push(d)
            }
            if (type == 'copy') {
              req.copyTableData('.testReportPrinting', sAy);
            } else {
              req.lodop(sAy);
            }
            break;
          case '考场布局表':
            var con = '';
            for (let obj of this.tableData) {
              var tablestr = '<table class="table table=bordered" style="margin-bottom:30px"><thead>',
                tableThTr = '<tr><th colspan="6">', tableTd = '', tTr = '';
              tableThTr += obj.room + '</th></tr><tr><th>考场名</th><th>行数</th><th>列数</th><th>考场座号</th><th>姓名</th><th>考号</th></tr>';
              for (let mbj of obj.seat) {
                tTr += '<tr><td>' + (mbj.roomName || '') + '</td><td>' + mbj.seatRow + '</td><td>' + mbj.seatCol + '</td><td>' + mbj.seatNumber + '</td><td>' + (mbj.name || '') + '</td><td>' + (mbj.number || '') + '</td></tr>';
              }
              tableTd += tTr;
              tablestr += tableThTr + '</thead><tbody>';
              tablestr += tableTd;
              tablestr += '</tbody></table>';
              con += tablestr;
            }
            if (type == 'copy') {
              if ($("#bstableCopy").length < 1) {
                var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
                $('.testReportPrinting').append(element);
              }
              $("#bstableCopy").html(con);
              $("#bstableCopy").select(); // 选择对象
              document.execCommand("Copy");
              alert('复制成功,请粘贴到Excel表格中！');
            } else {
              var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
              var printStyle, printContent;
              printStyle = "<style>" + printcss() + printCssStr + "</style>";
              printContent = printStyle + "<body>" + con + "</body>";

              var newWin = window.open("");//新打开一个空窗口
              newWin.document.write(printContent);//将表格添加进新的窗口
              newWin.document.close();//在IE浏览器中使用必须添加这一句
              newWin.focus();//在IE浏览器中使用必须添加这一句

              newWin.print();//打印
              newWin.close();//关闭窗口
            }
            break;
          case '座签表':
            var con = '';
            for (let obj of this.tableData) {
              var tablestr = '<table class="table table=bordered" style="margin-bottom:30px"><thead>',
                tableThTr = '<tr><th colspan="5">', tableTd = '', tTr = '';
              tableThTr += obj.room + '</th></tr><tr><th>考场</th><th>座号</th><th>姓名</th><th>考号</th><th>签名</th></tr>';
              for (let mbj of obj.seat) {
                tTr += '<tr><td>' + (mbj.roomName || '') + '</td><td>' + mbj.seatNumber + '</td><td>' + (mbj.name || '') + '</td><td>' + (mbj.number || '') + '</td><td></td></tr>';
              }
              tableTd += tTr;
              tablestr += tableThTr + '</thead><tbody>';
              tablestr += tableTd;
              tablestr += '</tbody></table>';
              con += tablestr;
            }
            if (type == 'copy') {
              if ($("#bstableCopy").length < 1) {
                var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
                $('.testReportPrinting').append(element);
              }
              $("#bstableCopy").html(con);
              $("#bstableCopy").select(); // 选择对象
              document.execCommand("Copy");
              alert('复制成功,请粘贴到Excel表格中！');
            } else {
              var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
              var printStyle, printContent;
              printStyle = "<style>" + printcss() + printCssStr + "</style>";
              printContent = printStyle + "<body>" + con + "</body>";

              var newWin = window.open("");//新打开一个空窗口
              newWin.document.write(printContent);//将表格添加进新的窗口
              newWin.document.close();//在IE浏览器中使用必须添加这一句
              newWin.focus();//在IE浏览器中使用必须添加这一句

              newWin.print();//打印
              newWin.close();//关闭窗口
            }
            break;
          case '分班考场安排表':
            var con = '';
            for (let obj of this.tableData) {
              var tablestr = '<table class="table table=bordered" style="margin-bottom:30px"><thead>',
                tableThTr = '<tr><th colspan="4">', tableTd = '', tTr = '';
              tableThTr += obj.className + '</th></tr><tr><th>序号</th><th>姓名</th><th>考号</th><th>考场名</th></tr>';
              for (let mbj of obj.data) {
                tTr += '<tr><td>' + (mbj.key || '') + '</td><td>' + (mbj.name || '') + '</td><td>' + (mbj.number || '') + '</td><td>' + (mbj.room || '') + '</td></tr>';
              }
              tableTd += tTr;
              tablestr += tableThTr + '</thead><tbody>';
              tablestr += tableTd;
              tablestr += '</tbody></table>';
              con += tablestr;
            }
            if (type == 'copy') {
              if ($("#bstableCopy").length < 1) {
                var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
                $('.testReportPrinting').append(element);
              }
              $("#bstableCopy").html(con);
              $("#bstableCopy").select(); // 选择对象
              document.execCommand("Copy");
              alert('复制成功,请粘贴到Excel表格中！');
            } else {
              var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
              var printStyle, printContent;
              printStyle = "<style>" + printcss() + printCssStr + "</style>";
              printContent = printStyle + "<body>" + con + "</body>";

              var newWin = window.open("");//新打开一个空窗口
              newWin.document.write(printContent);//将表格添加进新的窗口
              newWin.document.close();//在IE浏览器中使用必须添加这一句
              newWin.focus();//在IE浏览器中使用必须添加这一句

              newWin.print();//打印
              newWin.close();//关闭窗口
            }
            break;
          case '考试安排表':
            var con = '';
            for (let obj of this.tableData) {
              var tablestr = '<table class="table table=bordered" style="margin-bottom:30px"><thead>',
                tableThTr = '<tr><th colspan="6">', tableTd = '', tTr = '';
              tableThTr += obj.room + '</th></tr><tr><th>考场</th><th>班级</th><th>班级座号</th><th>考场座号</th><th>姓名</th><th>考号</th></tr>';
              for (let mbj of obj.value) {
                tTr += '<tr><td>' + (mbj.roomName || '') + '</td><td>' + (mbj.className || '') + '</td><td>' + (mbj.serialNumber || '') + '</td><td>' + (mbj.seatnumber || '') + '</td><td>' + (mbj.name || '') + '</td><td>' + (mbj.number || '') + '</td></tr>';
              }
              tableTd += tTr;
              tablestr += tableThTr + '</thead><tbody>';
              tablestr += tableTd;
              tablestr += '</tbody></table>';
              con += tablestr;
            }
            if (type == 'copy') {
              if ($("#bstableCopy").length < 1) {
                var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
                $('.testReportPrinting').append(element);
              }
              $("#bstableCopy").html(con);
              $("#bstableCopy").select(); // 选择对象
              document.execCommand("Copy");
              alert('复制成功,请粘贴到Excel表格中！');
            } else {
              var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
              var printStyle, printContent;
              printStyle = "<style>" + printcss() + printCssStr + "</style>";
              printContent = printStyle + "<body>" + con + "</body>";

              var newWin = window.open("");//新打开一个空窗口
              newWin.document.write(printContent);//将表格添加进新的窗口
              newWin.document.close();//在IE浏览器中使用必须添加这一句
              newWin.focus();//在IE浏览器中使用必须添加这一句

              newWin.print();//打印
              newWin.close();//关闭窗口
            }
            break;
          case '考场台卡':
            break;
          case '考签到表':
            var con = '';
            for (let obj of this.tableData) {
              var tablestr = '<table class="table table=bordered" style="margin-bottom:30px"><thead>',
                tableThTr = '<tr><th colspan="6">', tableTd = '', tTr = '';
              tableThTr += obj.bsname + ' ' + obj.room + ' ' + obj.teacher + '</th></tr><tr><th>座位号</th><th>姓名</th><th>签名</th></tr>';
              for (let mbj of obj.list) {
                tTr += '<tr><td>' + (mbj.serialNumber || '') + '</td><td>' + (mbj.name || '') + '</td><td></td></tr>';
              }
              tableTd += tTr;
              tablestr += tableThTr + '</thead><tbody>';
              tablestr += tableTd;
              tablestr += '</tbody></table>';
              con += tablestr;
            }
            if (type == 'copy') {
              if ($("#bstableCopy").length < 1) {
                var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
                $('.testReportPrinting').append(element);
              }
              $("#bstableCopy").html(con);
              $("#bstableCopy").select(); // 选择对象
              document.execCommand("Copy");
              alert('复制成功,请粘贴到Excel表格中！');
            } else {
              var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
              var printStyle, printContent;
              printStyle = "<style>" + printcss() + printCssStr + "</style>";
              printContent = printStyle + "<body>" + con + "</body>";

              var newWin = window.open("");//新打开一个空窗口
              newWin.document.write(printContent);//将表格添加进新的窗口
              newWin.document.close();//在IE浏览器中使用必须添加这一句
              newWin.focus();//在IE浏览器中使用必须添加这一句

              newWin.print();//打印
              newWin.close();//关闭窗口
            }
            break;
          case '监考任务安排统计表':
            hdData = {
              invigilator: '监考',
              visits: '巡考',
              totalinspection: '总巡考',
              all: '安排次数',
              time: '总时长',
              timeaverage: '均时长',
              name: '教师'
            };
            sAy.push(hdData);
            for (let obj of this.tableData) {
              let d = {};
              for (let name in hdData) {
                d[name] = obj[name] || '';
              }
              sAy.push(d)
            }
            if (type == 'copy') {
              req.copyTableData('.testReportPrinting', sAy);
            } else {
              req.lodop(sAy);
            }
            break;
          case '考务会签到表':
            hdData = {
              name: '姓名',
              phone: '电话'
            };
            sAy.push(hdData);
            for (let obj of this.tableData) {
              let d = {};
              for (let name in hdData) {
                d[name] = obj[name] || '';
              }
              sAy.push(d)
            }
            if (type == 'copy') {
              req.copyTableData('.testReportPrinting', sAy);
            } else {
              req.lodop(sAy);
            }
            break;
          case '上报学生名单':
          case '不上报学生名单':
            hdData = {
              name: '姓名',
              number: '考号',
              sex: '性别',
              branch: '科类',
              scool: '科类',
              hkle: '户籍类型',
              tesults: '总分'
            };
            sAy.push(hdData);
            for (let obj of this.tableData) {
              let d = {};
              for (let name in hdData) {
                d[name] = obj[name] || '';
              }
              sAy.push(d)
            }
            if (type == 'copy') {
              req.copyTableData('.testReportPrinting', sAy);
            } else {
              req.lodop(sAy);
            }
            break;
          case '不参考学生名单':
            hdData = {
              name: '姓名',
              className: '班级'
            };
            sAy.push(hdData);
            for (let obj of this.tableData) {
              let d = {};
              for (let name in hdData) {
                d[name] = obj[name] || '';
              }
              sAy.push(d)
            }
            if (type == 'copy') {
              req.copyTableData('.testReportPrinting', sAy);
            } else {
              req.lodop(sAy);
            }
            break;
          case '监考员座位卡':
            hdData = {
              name: '姓名'
            };
            sAy.push(hdData);
            for (let obj of this.tableData) {
              let d = {};
              for (let name in hdData) {
                d[name] = obj[name] || '';
              }
              sAy.push(d)
            }
            if (type == 'copy') {
              req.copyTableData('.testReportPrinting', sAy);
            } else {
              req.lodop(sAy);
            }
            break;
          case '参考学生总名册':
            var tablestr = '<table class="table table=bordered" style="margin-bottom:0"><thead>';
            let tableThTr = '<tr><th rowspan="2">姓名</th><th rowspan="2">年级</th><th rowspan="2">班级</th><th rowspan="2">考场</th><th rowspan="2">座位号</th><th rowspan="2">身份证</th><th rowspan="2">学校</th><th rowspan="2">考号</th><th colspan="2">考试科目</th>',
              tableThTr1 = '<tr>', tableTd = '';
            tableThTr1 += '<th>科目</th><th>老师</th>';
            for (let obj of this.tableData) {
              let tTr = '<tr>', n = '', t = '';
              tTr += '<td>' + (obj.name || '') + '</td><td>' + (obj.grade || '') + '</td><td>' + (obj.className || '') + '</td><td>' + (obj.room || '') + '</td><td>' + (obj.seatnumber || '') + '</td><td>' + (obj.idCard || '') + '</td><td>' + (obj.scName || '') + '</td><td>' + (obj.number || '') + '</td>';
              for (let mbj of obj.subjectlist) {
                n += (mbj.subject || '') + ' ';
                t += (mbj.teacher || '') + ' ';
              }
              tTr += '<td>' + n + '</td><td>' + t + '</td>';
              tTr += '</tr>';
              tableTd += tTr;
            }
            tableThTr += '</tr>';
            tableThTr1 += '</tr>';
            tablestr += tableThTr + tableThTr1 + '</thead><tbody>';
            tablestr += tableTd;
            tablestr += '</tbody></table>';
            if (type == 'copy') {
              if ($("#bstableCopy").length < 1) {
                var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
                $('.testReportPrinting').append(element);
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
            }
            break;
        }

        function printcss() {
          var css = ".table-bordered {border: 1px solid #000;}.table {width: 100%;max-width: 100%;margin-bottom: 40px;}.table {border-spacing: 0;border-collapse: collapse;}.table td{border: 1px solid #000;vertical-align: middle;text-align:center;font-size:15px;height:30px;}.table th{padding:6px; vertical-align: top; border: 1px solid #000;text-align:center;  font-weight:bold;  font-size:13px;}.table div{  text-align:center; } .table .w_35{width:35px; }.table .w_100{width:100px; }.editdiv{ width: 100%;  border: 0;  height: 30px;   font-size: 15px;  font-weight: normal;  text-align: center;  line-height:30px; }.ng-table-filters{display:none;};input{width: 100%;border: 0;height:30px;line-height:30px;font-size: 15px;font-weight: normal;text-align: center;}";
          return css;
        }
      }
    }
  }
</script>
<style>
  .testReportPrinting h2 {
    font-size: 1.5rem;
    margin-bottom: 2rem;
    text-align: center;
  }

  .testReportPrinting .dataList {
    width: 60%;
    margin: auto;
    min-height: 35rem;
  }

  .testReportPrinting .secTable {
    border: 1px solid #dfe6ec;
  }

  .testReportPrinting .resetThead {
    background-color: #deeefe;
    height: 2.5rem;
    line-height: 2.5rem;
    text-align: center;
  }

  .testReportPrinting .resetThead + .resetThead {
    border-left: 1px solid #dfe6ec;
  }

  .testReportPrinting .dataList .el-table th {
    background-color: #deeefe;
    height: 2.5rem;
  }

  .testReportPrinting .resetTd {
    height: 2.2rem;
    line-height: 2.2rem;
    font-size: .875rem;
    text-align: center;
    border-top: 1px solid #dfe6ec;
  }

  .testReportPrinting .resetTd + .resetTd {
    border-left: 1px solid #dfe6ec;
  }

  .testReportPrinting .dataList .el-table td {
    height: 2.2rem;
    font-size: .875rem;
  }

  .testReportPrinting .dataList .el-table__footer-wrapper thead div, .testReportPrinting .dataList .el-table__header-wrapper thead div {
    background-color: #deeefe;
    color: inherit;
  }

  .testReportPrinting .sec_head {
    text-align: center;
    padding: 1rem 0;
    font-size: 1.25rem;
  }

  .testReportPrinting .sec_head span + span {
    margin-left: 1rem;
  }

  .testReportPrinting .taiCard {
    border: 1px solid #d2d2d2;
    margin-top: 1rem;
    padding: 4rem 0;
  }

  .testReportPrinting .taiCard p {
    padding: 1rem 0;
    text-align: center;
  }
</style>
