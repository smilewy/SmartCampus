<template>
  <div class="printReport g-container printMessage">
    <header class="g-textHeader g-importCourseHeader">
      <div class="g-flexStartRow">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
          <img src="../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回流程图
        </el-button>
        <h2 class="selfCenter">打印报表</h2>
      </div>
    </header>
    <!--    <el-row>
          <el-form :inline="true" class="formInline">
            <el-form-item label="报表类型：">
              <el-select v-model="reportType" placeholder="请选择" class="reportType">
                <el-option
                  v-for="item in options"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </el-row>-->
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button-group>
        <el-button class="filt" title="复制" @click="operationData('copy')">
          <img class="filt_unactive"
               src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
               alt="">
          <img class="filt_active"
               src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
               alt="">
        </el-button>
        <el-button class="delete" title="打印" @click="operationData('print')">
          <img class="delete_unactive"
               src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
               alt="">
          <img class="delete_active"
               src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
               alt="">
        </el-button>
      </el-button-group>
    </el-row>
    <el-row class="volunteerConfirmList_row"v-for="(row,rowI) in gradeClassData" :key="rowI">
      <el-row class="listTitle">
        <h2 v-text="row.grade+row.className+'班（'+row.number+'人）'+row.level"></h2>
        <h2 v-text="'班主任：'+row.user"></h2>
      </el-row>
      <el-row :gutter="40" type="flex" justify="center">
        <el-col :span="10">
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            :data="row.stu_one"
            style="width: 100%"
            border
          >
            <el-table-column
              prop="serialNumber"
              label="班级序号">
            </el-table-column>
            <el-table-column
              prop="name"
              label="学生姓名">
            </el-table-column>
            <el-table-column
              prop="sex"
              label="性别">
            </el-table-column>
          </el-table>
        </el-col>
        <el-col :span="10">
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            :data="row.stu_two"
            style="width: 100%"
            border
          >
            <el-table-column
              prop="serialNumber"
              label="班级序号">
            </el-table-column>
            <el-table-column
              prop="name"
              label="学生姓名">
            </el-table-column>
            <el-table-column
              prop="sex"
              label="性别">
            </el-table-column>
          </el-table>
        </el-col>
      </el-row>
    </el-row>
    <el-row class="volunteerConfirmList_row" v-if="gradeNotClassObj">
      <el-row class="listTitle">
        <h2 v-text="gradeNotClassObj.className+'（'+gradeNotClassObj.total+'人）'"></h2>
      </el-row>
      <el-row :gutter="40" type="flex" justify="center">
        <el-col :span="10">
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            :data="gradeNotClassObj.stu_one"
            style="width: 100%"
            border
          >
            <el-table-column
              label="班级序号">
              <template slot-scope="prop">
                <span v-if="prop.row.serialNumber" v-text="prop.row.serialNumber"></span>
                <span v-else>**</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="name"
              label="学生姓名">
            </el-table-column>
            <el-table-column
              prop="sex"
              label="性别">
            </el-table-column>
          </el-table>
        </el-col>
        <el-col :span="10">
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            :data="gradeNotClassObj.stu_two"
            style="width: 100%"
            border
          >
            <el-table-column
              label="班级序号">
              <template slot-scope="prop">
                <span v-if="prop.row.serialNumber" v-text="prop.row.serialNumber"></span>
                <span v-else>**</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="name"
              label="学生姓名">
            </el-table-column>
            <el-table-column
              prop="sex"
              label="性别">
            </el-table-column>
          </el-table>
        </el-col>
      </el-row>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import {printMessageLoad} from '@/api/http'
  export default{
    data(){
      return {
        isLoading:false,
        tableData: [],
        tableDataNot: [],
        gradeClassData:[],
        gradeNotClassObj:null,
        options: [],
        reportType:'',
        /*send ajax param*/
        gradeId:'',
      }
    },
    methods: {
      operationData(type){
        var con = '';
        for (let obj of this.gradeClassData) {
          var tablestr = '<table class="table table=bordered" style="margin-bottom:30px"><thead>',
            tableThTr = '<tr><th colspan="6">', tableTd = '', tTr = '';
          tableThTr += obj.grade +' '+obj.className+' '+'班'+' '+'('+obj.number+'人'+')'+' '+ obj.level+' &nbsp; &nbsp; &nbsp;&nbsp;'+ '班主任：'+obj.user+
            '</th></tr><tr><th>班级序号</th><th>学生姓名</th><th>性别</th></tr>';
          for (let mbj of obj.stu) {
            tTr += '<tr><td>' + (mbj.serialNumber || '') + '</td><td>' + (mbj.name || '') + '</td><td>' + (mbj.sex || '') + '</td></tr>';
          }
          tableTd += tTr;
          tablestr += tableThTr + '</thead><tbody>';
          tablestr += tableTd;
          tablestr += '</tbody></table>';
          con += tablestr;
        }
        var tablestr = '<table class="table table=bordered" style="margin-bottom:30px"><thead>',
          tableThTr = '<tr><th colspan="6">', tableTd = '', tTr = '';
        tableThTr += this.gradeNotClassObj.className+'  '+'('+this.gradeNotClassObj.number+'人'+')'+
          '</th></tr><tr><th>班级序号</th><th>学生姓名</th><th>性别</th></tr>';
        for (let mbj of this.gradeNotClassObj.stu) {
          tTr += '<tr><td>' + (mbj.serialNumber || '') + '</td><td>' + (mbj.name || '') + '</td><td>' + (mbj.sex || '') + '</td></tr>';
        }
        tableTd += tTr;
        tablestr += tableThTr + '</thead><tbody>';
        tablestr += tableTd;
        tablestr += '</tbody></table>';
        con += tablestr;
        if (type == 'copy') {
          if ($("#bstableCopy").length < 1) {
            var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
            $('.printMessage').append(element);
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

        function printcss() {
          var css = ".table-bordered {border: 1px solid #000;}.table {width: 100%;max-width: 100%;margin-bottom: 40px;}.table {border-spacing: 0;border-collapse: collapse;}.table td{border: 1px solid #000;vertical-align: middle;text-align:center;font-size:15px;height:30px;}.table th{padding:6px; vertical-align: top; border: 1px solid #000;text-align:center;  font-weight:bold;  font-size:13px;}.table div{  text-align:center; } .table .w_35{width:35px; }.table .w_100{width:100px; }.editdiv{ width: 100%;  border: 0;  height: 30px;   font-size: 15px;  font-weight: normal;  text-align: center;  line-height:30px; }.ng-table-filters{display:none;};input{width: 100%;border: 0;height:30px;line-height:30px;font-size: 15px;font-weight: normal;text-align: center;}";
          return css;
        }
      },
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'newStudentClass'});
      },
      getLoadAjax(){
        this.isLoading=true;
        printMessageLoad({gradeId:this.gradeId}).then(data=>{
          if(data.status){
            data.data.forEach((row,rowI)=>{
              row.stu_one=row.stu.slice(0,Math.ceil(row.total/2));
              row.stu_two=row.stu.slice(Math.ceil(row.total/2));
            });
            data.not.stu_one=data.not.stu.slice(0,Math.ceil(data.not.total/2));
            data.not.stu_two=data.not.stu.slice(Math.ceil(data.not.total/2));
            this.gradeClassData=data.data;
            this.gradeNotClassObj=data.not;
          }
          else{
            this.vmMsgError('暂无数据');
            this.gradeClassData=[];
            this.gradeNotClassObj=null;
          }
          this.isLoading=false;
        });
      }
    },
    created(){
      this.gradeId=this.$route.params.gradeId;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-textHeader{
    h2{.marginLeft(40,1582);}
  }
  .printReport .volunteerConfirmList_row{
    margin-bottom:3.5rem;
  }
  .printReport .alertsBtn {
    margin: 1.25rem 0;
  }
  .printReport .el-table th{
    background-color: #deeefe;
    height:2.5rem;
  }
  .printReport .el-table td{
    height:2.5rem;
    font-size:.875rem;
  }
  .printReport .el-table__footer-wrapper thead div, .printReport .el-table__header-wrapper thead div{
    background-color: #deeefe;
    color: #282828;
  }
  .printReport .listTitle{
    text-align: center;
    margin-bottom:1.5rem;
  }
  .printReport .listTitle>p:first-child{
    font-size:1.5rem;
  }
  .printReport .listTitle>p:last-child{
    font-size:1.25rem;
    margin-top:1rem;
  }
  .printReport .reportType{
    width:12.5rem;
  }
  .printReport .pNumber{
    color: #4da1ff;
  }
</style>
