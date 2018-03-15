<template>
  <div class="examinationDistribution">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>考场分配</h3>
    </el-row>
    <el-row class="examManager_row">
      <span>参考人数：{{attendPerson.all}}人（<span
        v-for="attend in attendPerson.date">{{attend.branch}}：{{attend.num}}人;</span>）</span>
      <span>考场数：{{roomNum.all}}个（<span v-for="room in roomNum.data">{{room.branch}}：{{room.num}}个;</span>）</span>
      <span>座位数：{{seatsNum.all}}个（<span v-for="seat in seatsNum.data">{{seat.branch}}：{{seat.num}}个;</span>）</span>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button-group>
          <el-button class="filt" title="添加" @click="addMsg(0)">
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
            v-model="selectParam.screen"
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
        @selection-change="handleSelectionChange"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          type="selection"
          width="55">
        </el-table-column>
        <el-table-column
          type="index"
          width="80"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="branch"
          label="科类" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="room"
          label="考场" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="seats"
          label="安排座位数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="columns"
          label="安排座位列数" sortable="custom">
        </el-table-column>
        <el-table-column
          label="座位布局状态">
          <template slot-scope="scope">
            <span v-if="scope.row.status=='1'">已设置</span>
            <span v-if="scope.row.status=='0'">未设置</span>
          </template>
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="edit" @click="addMsg(1,scope.$index)">编辑</span>
            <span class="setSeat" @click="addMsg(2,scope.$index)">座位设置</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="selectParam.page"
        :page-size="selectParam.limit"
        layout="prev, pager, next, jumper"
        :total="totalNum">
      </el-pagination>
    </el-row>
    <el-dialog
      :title="messageTitle"
      :visible.sync="dialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row class="testNumber_dialog_body">
        <el-form :rules="editRules" ref="editForm" :model="message" label-width="120px">
          <el-form-item prop="branchid" label="科类：">
            <el-select v-model="message.branchid" placeholder="请选择">
              <el-option v-for="branch in branchlist" :label="branch.branch" :value="branch.branchid"
                         :key="branch.branchid"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item prop="room" label="考场：">
            <el-input v-model="message.room"></el-input>
          </el-form-item>
          <el-form-item prop="seats" label="安排座位数：">
            <el-input placeholder="座位数不能超过90，至少为1" v-model.number="message.seats"></el-input>
          </el-form-item>
          <el-form-item prop="columns" label="安排座位列数：">
            <el-input placeholder="座列数不能超过9，至少为1" v-model.number="message.columns"></el-input>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg(0)">保存</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="座位布局"
      :visible.sync="seatDialogVisible"
      :before-close="handleClose"
      :modal="false"
      class="seatLayout">
      <el-row>
        <el-form :inline="true" ref="seatLayoutForm" :rules="seatLayoutFormRules" :model="seatLayoutForm"
                 class="formInline">
          <el-form-item label="排序方法：">
            <el-select v-model="seatLayoutForm.sort" placeholder="请选择排序" @change="seatSetLayout">
              <el-option label="原排序" value="0"></el-option>
              <el-option label="S型排列" value="1"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item prop="id" label="考场：">
            <el-select multiple v-model="seatLayoutForm.id" placeholder="请选择考场">
              <el-option v-for="selectBranch in selectBranchList" :label="selectBranch.room" :value="selectBranch.id"
                         :key="selectBranch.id"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </el-row>
      <el-row class="d_line"></el-row>
      <el-row class="rostrum">
        <span class="rostrumPosition">讲台</span>
      </el-row>
      <dragable :dataList.sync="test" @getPos="getPosAry"></dragable>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg(1)">保存</el-button>
        <el-button @click="closeSeatPop(1)">关闭</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import dragable from './drag.vue'

  export default {
    components: {
      dragable
    },
    data() {
      var checkSeats = (rule, value, callback) => {
        if (!value) {
          return callback(new Error('座位数不能为空'));
        }
        if (!Number.isInteger(value)) {
          callback(new Error('请输入数字值'));
        } else {
          if (value <= 0) {
            callback(new Error('座位数最少为1'));
          }
          if (value > 90) {
            callback(new Error('座位数最大为90'));
          } else {
            callback();
          }
        }
      };
      var checkColumns = (rule, value, callback) => {
        if (!value) {
          return callback(new Error('座位列数不能为空'));
        }
        if (!Number.isInteger(value)) {
          callback(new Error('请输入数字值'));
        } else {
          if (value <= 0) {
            callback(new Error('座位列数最少为1'));
          }
          if (value > 9) {
            callback(new Error('座位列数最大为9'));
          } else {
            callback();
          }
        }
      };
      var checkRoom = (rule, value, callback) => {
        if (value.length == 0) {
          return callback(new Error('请选择考场'));
        } else {
          callback();
        }
      };
      return {
        attendPerson: {},
        roomNum: {},
        seatsNum: {},
        tableData: [],
        branchlist: [],
        selectParam: {
          page: 1,
          limit: 50,
          field: '',
          examinationid: '',
          order: '',
          screen: ''
        },
        multipleSelection: [],
        totalNum: 0,
        messageTitle: '',
        message: {
          id: '',
          branchid: '',
          room: '',
          seats: '',
          columns: '',
          examinationid: ''
        },
        editRules: {
          branchid: [
            {required: true, message: '请选择科类', trigger: 'change'}
          ],
          room: [
            {required: true, message: '请填写考场名字', trigger: 'blur'},
            {min: 1, max: 10, message: '长度在 1 到 10 个字符', trigger: 'blur'}
          ],
          seats: [
            {required: true, validator: checkSeats, trigger: 'blur'},
          ],
          columns: [
            {required: true, validator: checkColumns, trigger: 'blur'}
          ]
        },
        dialogVisible: false,
        seatDialogVisible: false,
        seatLayoutForm: {
          id: '',
          seat: [],
          examinationid: '',
          roomid: '',
          sort: '0'
        },
        seatLayoutFormRules: {
          id: {required: true, validator: checkRoom, trigger: 'change'}
        },
        selectBranchList: [],
        hasBeenSetTest: [],
        test: [],
        testParam: {
          status: '0',
          column: 0,
          seatsNum: 0,
          remainder: 0,
          minRow: 0,
          rows: 0
        },
        posAry: [],
        loading: false
      }
    },
    created: function () {
      this.selectParam.examinationid = this.$route.params.examinationid;
      //查询考场数据
      this.loadData(this.selectParam);
    },
    methods: {
      returnFlowchart() {
        this.$router.push('/examManagerHome');
      },
      goSearch() {  //查询
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.selectParam.page = 1;
        this.loadData(this.selectParam);
      },
      sort(column) {
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      handleClose(done) {   //关闭弹框座位
        done();
        if (!this.seatDialogVisible) {
          this.closeSeatPop(0);
        }
      },
      getPosAry(ary){  //获取座位的初始位置数组
        this.posAry = ary;
      },
      addMsg(type, idx) {
        if (type == 0) {  //添加
          this.messageTitle = '添加信息';
          this.dialogVisible = true;
          for (let ob in this.message) {
            this.message[ob] = '';
          }
          this.message.examinationid = this.selectParam.examinationid;
        } else if (type == 1) {   //修改
          this.messageTitle = '修改信息';
          this.dialogVisible = true;
          this.message.id = this.tableData[idx].id;
          this.message.room = this.tableData[idx].room;
          this.message.seats = !this.tableData[idx].seats ? '' : Number.parseInt(this.tableData[idx].seats);
          this.message.columns = !this.tableData[idx].columns ? '' : Number.parseInt(this.tableData[idx].columns);
          this.message.examinationid = '';
          for (let obj of this.branchlist) {
            if (obj.branch == this.tableData[idx].branch) {
              this.message.branchid = obj.branchid;
            }
          }
        } else {   //设置座位布局
          var self = this, tData = {
            examinationid: self.selectParam.examinationid
          };
          if (!self.tableData[idx].seats) {
            self.vmMsgWarning('请先设置座位数和座位列数！');
            return false;
          }
          self.testParam.status = self.tableData[idx].status;
          self.testParam.column = Number.parseInt(self.tableData[idx].columns);
          self.testParam.seatsNum = Number.parseInt(self.tableData[idx].seats);
          self.testParam.remainder = self.testParam.seatsNum % self.testParam.column;
          self.testParam.minRow = Number.parseInt(self.testParam.seatsNum / self.testParam.column);
          self.testParam.rows = self.testParam.remainder == 0 ? self.testParam.minRow : (self.testParam.minRow + 1);
          self.seatDialogVisible = true;
          self.seatLayoutForm.id = [self.tableData[idx].id];
          self.seatLayoutForm.roomid = self.tableData[idx].id;
          self.seatLayoutForm.sort = self.tableData[idx].sort;
          //请求可选考场
          req.ajaxSend('/school/Examination/exmanagement/type/examroom/typename/seatroom', 'post', tData, function (res) {
            self.selectBranchList = res;
          });
          self.test = self.initTestAry();
          if (self.testParam.status == '0') {  //未设置
            //将安排的座位显示
            for (let j = 0; j < self.testParam.rows; j++) {   //行
              for (let k = 0; k < self.testParam.column; k++) {  //列
                let num;
                if (self.testParam.remainder != 0) {
                  if (k < self.testParam.remainder) {
                    num = k * self.testParam.rows + j + 1;
                  } else {
                    if (j < self.testParam.minRow) {
                      num = self.testParam.remainder * self.testParam.rows + (k - self.testParam.remainder) * self.testParam.minRow + j + 1;
                    } else {
                      num = '';
                    }
                  }
                } else {
                  num = k * self.testParam.rows + j + 1;
                }
                if (num <= self.testParam.seatsNum) {
                  if ((k + 1) % 2 == 0 && self.seatLayoutForm.sort == '1') {
                    if (self.testParam.remainder != 0) {
                      if (k < self.testParam.remainder) {
                        self.test[(self.testParam.rows - (j + 1)) * 11 + k].seatNumber = num;
                      } else {
                        if ((self.testParam.rows - (j + 2)) * 11 + k >= 0) {
                          self.test[(self.testParam.rows - (j + 2)) * 11 + k].seatNumber = num;
                        }
                      }
                    } else {
                      self.test[(self.testParam.rows - (j + 1)) * 11 + k].seatNumber = num;
                    }
                  } else {
                    self.test[j * 11 + k].seatNumber = num;
                  }
                } else {
                  self.test[j * 11 + k].seatNumber = '';
                }
              }
            }
          } else {   //已设置
            var idData = {
              id: self.tableData[idx].id
            };
            req.ajaxSend('/school/Examination/exmanagement/type/examroom/typename/roomseatfind', 'post', idData, function (res) {
              self.hasBeenSetTest = res;
              for (let ob of res) {
                let m = (Number.parseInt(ob.seatRow) - 1) * 11;
                let n = Number.parseInt(ob.seatCol) - 1;
                self.test[m + n].seatNumber = ob.seatNumber;
              }
            })
          }
          let lAry = $('.seatBody .seatRow');
          for (let [key, obj] of self.posAry.entries()) {
            lAry.eq(key).attr("index", obj.index);
            lAry[key].style.left = obj.left;
            lAry[key].style.top = obj.top;
          }
        }
      },
      seatSetLayout(){
        //将安排的座位显示
        this.test = this.initTestAry();
        for (let j = 0; j < this.testParam.rows; j++) {   //行
          for (let k = 0; k < this.testParam.column; k++) {  //列
            let num;
            if (this.testParam.remainder != 0) {
              if (k < this.testParam.remainder) {
                num = k * this.testParam.rows + j + 1;
              } else {
                if (j < this.testParam.minRow) {
                  num = this.testParam.remainder * this.testParam.rows + (k - this.testParam.remainder) * this.testParam.minRow + j + 1;
                } else {
                  num = '';
                }
              }
            } else {
              num = k * this.testParam.rows + j + 1;
            }
            if (num <= this.testParam.seatsNum) {
              if ((k + 1) % 2 == 0 && this.seatLayoutForm.sort == '1') {
                if (this.testParam.remainder != 0) {
                  if (k < this.testParam.remainder) {
                    this.test[(this.testParam.rows - (j + 1)) * 11 + k].seatNumber = num;
                  } else {
                    if ((this.testParam.rows - (j + 2)) * 11 + k >= 0) {
                      this.test[(this.testParam.rows - (j + 2)) * 11 + k].seatNumber = num;
                    }
                  }
                } else {
                  this.test[(this.testParam.rows - (j + 1)) * 11 + k].seatNumber = num;
                }
              } else {
                this.test[j * 11 + k].seatNumber = num;
              }
            } else {
              this.test[j * 11 + k].seatNumber = '';
            }
          }
        }
      },
      initTestAry(){  //初始化座位安排数据
        let test = [];
        for (let n = 1; n <= 12; n++) {  //行
          for (let m = 1; m <= 11; m++) {  //列
            let ay = {
              seatRow: n,
              seatCol: m,
              seatNumber: ''
            };
            test.push(ay);
          }
        }
        return test;
      },
      deleteAlerts() {
        var self = this;
        if (self.multipleSelection.length == 0) {
          self.vmMsgWarning('请选择记录！');
          return false;
        }
        self.$confirm('确定删除该考场?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          var ary = [];
          for (let select of self.multipleSelection) {
            ary.push(select.id);
          }
          var tParam = {
            id: ary.join(',')
          };
          req.ajaxSend('/school/Examination/exmanagement/type/examroom/typename/roomdel', 'post', tParam, function (res) {
            if (res.return) {
              self.vmMsgSuccess('删除成功!');
              self.loadData(self.selectParam);
            } else {
              self.vmMsgError('删除失败!');
            }
          });
        }).catch(() => {
        });
      },
      closeSeatPop(type) {   //关闭弹框座位
        if (type == 1) {
          this.seatDialogVisible = false;
        }
        for (let n = 0; n < this.test.length; n++) {
          this.test[n].seatNumber = '';
        }
      },
      saveMsg(type) {
        var self = this;
        if (type == 0) {
          self.$refs['editForm'].validate((valid) => {
            if (valid) {
              if (self.message.seats < self.message.columns) {
                self.vmMsgWarning('座位数必须>=座位列数！');
                return false;
              }
              if (self.message.seats / self.message.columns > 10) {
                self.vmMsgWarning('座位数÷座位列数不能超过10');
                return false;
              }
              req.ajaxSend('/school/Examination/exmanagement/type/examroom/typename/roomupin', 'post', self.message, function (res) {
                if (res.return) {
                  let msgSuc = (self.messageTitle == '修改信息' ? '修改成功!' : '添加成功');
                  self.vmMsgSuccess(msgSuc);
                  self.dialogVisible = false;
                  self.loadData(self.selectParam);
                } else {
                  let msgEro = (self.messageTitle == '修改信息' ? '修改失败!' : '添加失败');
                  self.vmMsgError(msgEro);
                }
              });
            } else {
              return false;
            }
          });
        } else {
          self.$refs['seatLayoutForm'].validate((valid) => {
            if (valid) {
              self.seatLayoutForm.seat = [];
              $('.seatRow').each(function () {
                if ($(this).attr('data-num')) {
                  let obj = {
                    seatRow: $(this).attr('data-row'),
                    seatCol: $(this).attr('data-col'),
                    seatNumber: $(this).attr('data-num')
                  };
                  self.seatLayoutForm.seat.push(obj);
                }
              });
              self.seatLayoutForm.examinationid = self.selectParam.examinationid;
              req.ajaxSend('/school/Examination/exmanagement/type/examroom/typename/roomseatup', 'post', self.seatLayoutForm, function (res) {
                if (res.return) {
                  self.vmMsgSuccess('设置成功！');
                  self.seatDialogVisible = false;
                  self.loadData(self.selectParam);
                } else {
                  self.vmMsgError('设置失败！');
                }
              });
            } else {
              return false;
            }
          });
        }
      },
      operationData(type){
        let sAy = [], hdData = {
          branch: '科类',
          room: '考场',
          seats: '安排座位数',
          columns: '安排座位列数',
          status: '座位布局状态'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name == 'status') {
              d[name] = obj[name] == '1' ? '已设置' : '未设置';
            } else {
              d[name] = obj[name] || '';
            }
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.examinationDistribution', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(param) {
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/examroom/typename/roomselect', 'post', param, function (res) {
          self.loading = false;
          self.attendPerson = res.attend;
          self.roomNum = res.room;
          self.seatsNum = res.seats;
          self.tableData = res.roomlist;
          self.branchlist = res.branchlist;
          self.totalNum = Number.parseInt(res.page.count);
        })
      }
    }
  }
</script>
<style>

  .examinationDistribution .seatLayout_btn.el-button--primary {
    background-color: #13b5b1;
    border-color: #13b5b1;
    height: 30px;
    padding: 0 15px;
  }

  .examinationDistribution .el-dialog--large {
    width: 1000px;
  }

  .examinationDistribution .seatLayout .el-dialog__footer {
    -webkit-box-shadow: 0 -8px 30px -10px #d2d2d2;
    -moz-box-shadow: 0 -8px 30px -10px #d2d2d2;
    box-shadow: 0 -8px 30px -10px #d2d2d2;
    text-align: right;
  }

  .examinationDistribution .rostrum {
    text-align: center;
  }

  .examinationDistribution .rostrumPosition {
    display: inline-block;
    border: 1px solid #89bcf5;
    border-radius: 6px;
    color: #89bcf5;
    padding: 10px 40px;
  }

  .examinationDistribution .seatLayout .el-form-item {
    margin-bottom: 0;
  }

  .examinationDistribution .testNumber_dialog_body {
    padding: 0 60px;
  }

  .examinationDistribution .edit {
    color: #20a0ff;
    cursor: pointer;
  }

  .examinationDistribution .setSeat {
    color: #ff5b5a;
    cursor: pointer;
  }
</style>
