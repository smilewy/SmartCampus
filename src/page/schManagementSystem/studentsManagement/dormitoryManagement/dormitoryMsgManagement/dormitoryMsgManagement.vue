<template>
  <div class="dormitoryMsgManagement">
    <h3>宿舍信息管理</h3>
    <el-row class="d_line dormitoryMsgManagement_row"></el-row>
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
          <el-button class="delete" title="删除" @click="deleteAlerts">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png"
                 alt="">
          </el-button>
        </el-button-group>
        <el-button class="secBtn-group filt" title="批量导入" @click="toNext">
          <img class="filt_unactive"
               src="../../../../../assets/img/schManagementSystem/studentsManagement/dormitoryManagement/icon_import.png"
               alt="">
          <img class="filt_active"
               src="../../../../../assets/img/schManagementSystem/studentsManagement/dormitoryManagement/icon_import_linght.png"
               alt="">
        </el-button>
      </el-col>
      <el-col :span="6">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入关键字"
            suffix-icon="el-icon-search"
            v-model="selectParam.key"
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
          prop="number"
          label="栋号"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          label="宿舍楼名称"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="floor"
          label="楼层"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="dormNumber"
          label="宿舍号"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="dormName"
          label="宿舍名称"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="dormType"
          label="宿舍类型"
          sortable="custom">
          <template slot-scope="scope">
            <span v-if="scope.row.dormType==1">女生宿舍</span>
            <span v-if="scope.row.dormType==2">男生宿舍</span>
            <span v-if="scope.row.dormType==3">混合宿舍</span>
            <span v-if="scope.row.dormType==4">其他</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="capacity"
          label="容纳人数"
          sortable="custom">
        </el-table-column>
        <el-table-column
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
        :page-size="selectParam.count"
        layout="prev, pager, next, jumper"
        :total="totalNum">
      </el-pagination>
    </el-row>
    <el-dialog
      :title="formTitle"
      :visible.sync="dialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row type="flex" justify="center">
        <el-col :span="16">
          <el-form ref="form" :model="form" :rules="formRule" label-width="110px">
            <el-form-item label="栋号：" prop="number">
              <el-input v-model="form.number" placeholder="请输入栋号"></el-input>
            </el-form-item>
            <el-form-item label="宿舍楼名称：" prop="name">
              <el-input v-model="form.name" placeholder="请输入宿舍楼名称"></el-input>
            </el-form-item>
            <el-form-item label="楼层：" prop="floor">
              <el-input v-model="form.floor" placeholder="请输入楼层"></el-input>
            </el-form-item>
            <el-form-item label="宿舍号：" prop="dormNumber">
              <el-input v-model="form.dormNumber" placeholder="请输入宿舍号"></el-input>
            </el-form-item>
            <el-form-item label="宿舍名称：" prop="dormName">
              <el-input v-model="form.dormName" placeholder="请输入宿舍名称"></el-input>
            </el-form-item>
            <el-form-item label="宿舍类型：" prop="dormType">
              <el-select v-model="form.dormType" placeholder="请选择" style="width:100%;">
                <el-option label="女生宿舍" value="1"></el-option>
                <el-option label="男生宿舍" value="2"></el-option>
                <el-option label="混合宿舍" value="3"></el-option>
                <el-option label="其他" value="4"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="容纳人数：" prop="capacity">
              <el-input v-model="form.capacity" placeholder="请输入容纳人数"></el-input>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg">保存</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      var reg = /^[1-9]\d*$/;
      var checkNum = (rule, value, callback) => {
        if (!reg.test(value)) {
          return callback(new Error('请输入容纳人数，为数字且大于0'));
        } else {
          callback();
        }
      };
      return {
        tableData: [],
        totalNum: 0,
        selectParam: {
          page: 1,
          count: 50,
          according: '',
          order: '',
          key: '',
        },
        multipleSelection: [],
        dialogVisible: false,
        form: {
          id: '',
          type: '',
          number: '',
          name: '',
          floor: '',
          dormNumber: '',
          dormType: '',
          dormName: '',
          capacity: ''
        },
        formTitle: '',
        formRule: {
          name: [
            {required: true, message: '请输入宿舍楼名称', trigger: 'blur'}
          ],
          dormType: [
            {required: true, message: '请选择宿舍类型', trigger: 'change'}
          ],
          number: [
            {required: true, message: '请输入栋号', trigger: 'blur'}
          ],
          floor: [
            {required: true, message: '请输入楼层', trigger: 'blur'}
          ],
          dormNumber: [
            {required: true, message: '请输入宿舍号', trigger: 'blur'}
          ],
          dormName: [
            {required: true, message: '请输入宿舍名称', trigger: 'blur'}
          ],
          capacity: [
            {required: true, validator: checkNum, trigger: 'blur'}
          ]
        },
        loading: false
      }
    },
    created: function () {
      this.loadData(this.selectParam);
    },
    methods: {
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.according = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      sort(column){
        this.selectParam.according = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      handleClose(done) {   //关闭弹框
        done();
      },
      addMsg(type, val){
        this.dialogVisible = true;
        if (type == 0) {
          this.formTitle = '添加';
          for (let ob in this.form) {
            this.form[ob] = '';
          }
          this.form.type = 'add';
        } else {
          this.formTitle = '信息编辑';
          for (let name in this.form) {
            this.form[name] = this.tableData[val][name];
          }
          this.form.type = 'edit';
        }
      },
      saveMsg(){
        var self = this;
        this.$refs['form'].validate((valid) => {
          if (valid) {
            req.ajaxSend('/school/StudentDorm/dormManage', 'post', self.form, function (res) {
              if (res.status == 1) {
                let mg = self.formTitle == '信息编辑' ? '修改成功！' : '添加成功！';
                self.vmMsgSuccess(mg);
                self.dialogVisible = false;
                self.loadData(self.selectParam);
              } else {
                self.vmMsgError(res.msg);
              }
            })
          } else {
            return false;
          }
        });
      },
      deleteAlerts(){
        var self = this, data = {
          type: 'del',
          ids: []
        };
        if (self.multipleSelection.length == 0) {
          self.vmMsgWarning('请选择记录！');
          return false;
        }
        for (let obj of self.multipleSelection) {
          data.ids.push(obj.id);
        }
        self.$confirm('确定删除?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/StudentDorm/dormManage', 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('删除成功！');
              self.loadData(self.selectParam);
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }).catch(() => {
        });
      },
      toNext(){
        this.$router.push({name: 'dormitoryImport'})
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/StudentDorm/dormManage', 'post', data, function (res) {
          self.tableData = res.data;
          self.totalNum = res.total;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .dormitoryMsgManagement {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .dormitoryMsgManagement h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
    display: inline-block;
  }

  .dormitoryMsgManagement .dormitoryMsgManagement_row {
    margin-top: 2rem;
  }

  .dormitoryMsgManagement .alertsList .el-table th, .dormitoryMsgManagement .alertsList .el-table td {
    text-align: center;
  }

  .dormitoryMsgManagement .alertsBtn {
    margin: 1.25rem 0;
  }

  .dormitoryMsgManagement .edit {
    color: #4da1ff;
    cursor: pointer;
  }

  .dormitoryMsgManagement .el-dialog--small {
    width: 600px;
  }
</style>
