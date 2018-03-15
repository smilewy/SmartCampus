<template>
  <div class="messageManagement">
    <h3>信息管理</h3>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button-group>
          <el-button class="filt" @click="addMsg(0)" title="添加">
            <img class="filt_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png"
                 alt="">
            <img class="filt_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="导出" @click="exportFile">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="删除" @click="deleteAlerts">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png"
                 alt="">
          </el-button>
        </el-button-group>
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
            placeholder="请输入任教科目/姓名"
            suffix-icon="el-icon-search"
            v-model="selectParam.value"
            @change="goSearch">
          </el-input>
        </div>
      </el-col>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        @selection-change="handleSelectionChange"
        @sort-change="sort"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          type="selection"
          width="55">
        </el-table-column>
        <el-table-column
          prop="name"
          min-width="120"
          label="姓名"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="sex"
          min-width="100"
          label="性别"
          sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="120"
          prop="teachingSubjects"
          label="任教科目"
          sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="120"
          prop="phone"
          label="手机号码"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="politics"
          min-width="120"
          label="政治面貌"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="nation"
          min-width="100"
          label="民族"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="birth"
          min-width="120"
          label="出生日期"
          sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="150"
          label="籍贯">
          <template slot-scope="scope">
            <span>{{scope.row.origin.province}} {{scope.row.origin.city}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="idCardType"
          min-width="150"
          label="身份证类型"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="idCard"
          min-width="180"
          label="身份证号"
          sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="150"
          label="户口所在地">
          <template slot-scope="scope">
            <span>{{scope.row.registerAddress.province}} {{scope.row.registerAddress.city}} {{scope.row.registerAddress.area}} {{scope.row.registerAddress.detail}}</span>
          </template>
        </el-table-column>
        <el-table-column
          min-width="150"
          label="家庭住址">
          <template slot-scope="scope">
            <span>{{scope.row.homeAddress.province}} {{scope.row.homeAddress.city}} {{scope.row.homeAddress.area}} {{scope.row.homeAddress.detail}}</span>
          </template>
        </el-table-column>
        <el-table-column
          min-width="150"
          label="现住地址">
          <template slot-scope="scope">
            <span>{{scope.row.nowAddress.province}} {{scope.row.nowAddress.city}} {{scope.row.nowAddress.area}} {{scope.row.nowAddress.detail}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="department"
          min-width="120"
          label="部门"
          sortable="custom">
        </el-table-column>
        <el-table-column
          fixed="right"
          label="操作">
          <template slot-scope="scope">
            <span class="edit" @click="addMsg(1,scope.$index)">编辑</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="selectParam.page"
        :page-size="selectParam.pageSize"
        layout="prev, pager, next, jumper"
        :total="totalNum">
      </el-pagination>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        tableData: [],
        totalNum: 0,
        multipleSelection: [],
        form: {
          name: '',
          sex: '',
          teachingSubjects: '',
          department: '',
          jobNumber: '',
          politics: '',
          phone: ''
        },
        selectParam: {
          page: 1,
          pageSize: 50,
          sort: '',
          sortType: '',
          value: ''
        },
        loading: false
      }
    },
    computed: {},
    created: function () {
      var self = this;
      //查询数据列表
      self.loadDataList(self.selectParam);
    },
    methods: {
      exportFile(){
        req.downloadFile('.messageManagement', '/school/user/exportTeacherOrZg?type=teacher', 'post');
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.sortType = '';
        this.selectParam.sort = '';
        this.loadDataList(this.selectParam);
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      sort(column){
        this.selectParam.sortType = column.prop || '';
        this.selectParam.sort = column.order || '';
        this.loadDataList(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadDataList(this.selectParam);
      },
      handleClose(done) {   //关闭弹框
        done();
      },
      addMsg(type, val){
        if (type == 0) {
          this.$router.push({name: 'teacherMessageAddOrEdit', params: {type: 0, id: 1}});
        } else {
          this.$router.push({name: 'teacherMessageAddOrEdit', params: {type: 1, id: this.tableData[val].id}});
        }
      },
      deleteAlerts(){
        var self = this;
        if (self.multipleSelection.length == 0) {
          self.vmMsgWarning('请选择记录！');
          return false;
        }
        self.$confirm('是否删除教师信息（删除后不能恢复）?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          var ary = [], userId;
          for (let obj of self.multipleSelection) {
            ary.push(obj.id);
          }
          userId = {
            userId: ary
          };
          req.ajaxSend('/school/user/userGl?type=deleteTethe', 'post', userId, function (res) {
            if (res.statu == 1) {
              self.vmMsgSuccess('删除成功!');
              self.selectParam.page = 1;
              self.loadDataList(self.selectParam);
            } else {
              self.vmMsgError(res.message);
            }
          });
        }).catch(() => {
        });
      },
      operationData(type){
        var self = this, formData = new FormData();
        let sAy = [], hdData;
        hdData = {
          name: '姓名',
          sex: '性别',
          nation: '民族',
          birth: '出生日期',
          teachingSubjects: '任教科目',
          department: '部门',
          phone: '手机号码',
          politics: '政治面貌',
          idCardType: '身份证类型',
          idCard: '身份证号',
          origin: '籍贯',
          registerAddress: '户口所在地',
          homeAddress: '家庭住址',
          nowAddress: '现住地址'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            for (let name in hdData) {
              if (name == 'origin') {
                d[name] = (obj[name].province || '') + ' ' + (obj[name].city || '');
              } else if (name == 'registerAddress' || name == 'homeAddress' || name == 'nowAddress') {
                d[name] = (obj[name].province || '') + ' ' + (obj[name].city || '') + ' ' + (obj.registerAddress.area || '') + ' ' + (obj.registerAddress.detail || '');
              } else {
                d[name] = obj[name] || '';
              }
            }
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.messageManagement', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadDataList(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/user/userGl?type=teacherList', 'get', data, function (res) {
          self.loading = false;
          self.tableData = res.data;
          self.totalNum = res.count;
        })
      }
    }
  }
</script>
<style>
  .messageManagement {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .messageManagement h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .messageManagement .g-fuzzyInput {
    float: right;
  }

  .messageManagement .edit {
    color: #ff5b5a;
    cursor: pointer;
  }
</style>
