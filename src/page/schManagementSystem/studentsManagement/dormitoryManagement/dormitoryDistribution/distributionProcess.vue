<template>
  <div class="distributionProcess">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回</span></el-button>
      <h3>宿舍分配流程</h3>
    </el-row>
    <el-row type="flex" justify="end" class="steps">
      <span class="step unable_edit">不可编辑</span>
      <span class="step completed">已完成</span>
      <span class="step main_process">主要流程</span>
      <span class="step sec_process">次要流程</span>
      <span class="step un_activated ">未激活</span>
    </el-row>
    <el-row class="stepLists">
      <el-row class="distributionProcess_items">
        <div class="distributionProcess_items topMax">
          <div class="distributionProcess_item">
            <router-link :to="{name:'distributionPersonnelList',params:{planId:planId}}" tag="div"
                         v-if="stateList[0].status!='-1'" class="item"
                         :class="{'completed':stateList[0].status=='1','unable_edit':stateList[0].status=='0','main_process':stateList[0].status=='2','sec_process':stateList[0].status=='3'}">
              设置分配人员名单
            </router-link>
            <div class="item un_activated" v-if="stateList[0].status=='-1'">设置分配人员名单</div>
          </div>
          <div>
            <div class="arrowRight horizontal_img unable_edit">
              <i class="el-icon-arrow-right"></i>
            </div>
          </div>
        </div>
        <div>
          <el-row>
            <div class="arrowRight independent_img1 unable_edit">
              <i class="el-icon-arrow-right"></i>
            </div>
          </el-row>
          <el-row class="distributionProcess_items">
            <div class="distributionProcess_item">
              <router-link :to="{name:'distributionDormitoryMsg',params:{planId:planId}}" tag="div"
                           v-if="stateList[1].status!='-1'" class="item"
                           :class="{'completed':stateList[1].status=='1','unable_edit':stateList[1].status=='0','main_process':stateList[1].status=='2','sec_process':stateList[1].status=='3'}">
                设置分配宿舍信息
              </router-link>
              <div class="item un_activated" v-if="stateList[1].status=='-1'">设置分配宿舍信息</div>
            </div>
            <div>
              <div class="arrowRight horizontal_img unable_edit">
                <i class="el-icon-arrow-right"></i>
              </div>
            </div>
          </el-row>
        </div>
        <div>
          <div class="spec_row">
            <el-row class="distributionProcess_item">
              <router-link :to="{name:'specifiedStudentDormitory',params:{planId:planId}}" tag="div"
                           v-if="stateList[2].status!='-1'" class="item"
                           :class="{'completed':stateList[2].status=='1','unable_edit':stateList[2].status=='0','main_process':stateList[2].status=='2','sec_process':stateList[2].status=='3'}">
                指定学生到宿舍
              </router-link>
              <div class="item un_activated" v-if="stateList[2].status=='-1'">指定学生到宿舍</div>
            </el-row>
            <el-row>
              <div class="arrowDown vertical_img unable_edit">
                <i class="el-icon-arrow-down"></i>
              </div>
            </el-row>
          </div>
          <el-row class="distributionProcess_item">
            <div>
              <router-link :to="{name:'fastDistributionDormitory',params:{planId:planId}}" tag="div"
                           v-if="stateList[3].status!='-1'" class="item"
                           :class="{'completed':stateList[3].status=='1','unable_edit':stateList[3].status=='0','main_process':stateList[3].status=='2','sec_process':stateList[3].status=='3'}">
                快速分配宿舍
              </router-link>
              <div class="item un_activated" v-if="stateList[3].status=='-1'">快速分配宿舍</div>
              <div class="arrowRight arrowRight_inline horizontal_img unable_edit">
                <i class="el-icon-arrow-right"></i>
              </div>
            </div>
            <div>
              <div class="arrowRightBot1 arrowRightBot spec_img unable_edit">
                <i class="el-icon-arrow-right"></i>
              </div>
              <div class="arrowRightBot spec_img unable_edit">
                <i class="el-icon-arrow-right"></i>
              </div>
            </div>
          </el-row>
        </div>
        <div class="distributionProcess_items topMax">
          <div>
            <el-row class="distributionProcess_item">
              <div class="item" v-if="stateList[4].status!='-1'"
                   :class="{'completed':stateList[4].status=='1','unable_edit':stateList[4].status=='0','main_process':stateList[4].status=='2','sec_process':stateList[4].status=='3'}"
                   @click="publishedMsg">发布宿舍信息
              </div>
              <div class="item un_activated" v-if="stateList[4].status=='-1'">发布宿舍信息</div>
            </el-row>
            <el-row>
              <div class="arrowTop vertical_img unable_edit">
                <i class="el-icon-arrow-up"></i>
              </div>
            </el-row>
            <el-row class="distributionProcess_item">
              <router-link :to="{name:'manuallyAdjustmentDormitory',params:{planId:planId}}" tag="div"
                           v-if="stateList[5].status!='-1'" class="item"
                           :class="{'completed':stateList[5].status=='1','unable_edit':stateList[5].status=='0','main_process':stateList[5].status=='2','sec_process':stateList[5].status=='3'}">
                手动调整
              </router-link>
              <div class="item un_activated" v-if="stateList[5].status=='-1'">手动调整</div>
            </el-row>
            <el-row>
              <div class="arrowDown vertical_img unable_edit">
                <i class="el-icon-arrow-down"></i>
              </div>
            </el-row>
            <el-row class="distributionProcess_item">
              <router-link :to="{name:'dormitoryPrintReport',params:{planId:planId}}" tag="div"
                           v-if="stateList[6].status!='-1'" class="item"
                           :class="{'completed':stateList[6].status=='1','unable_edit':stateList[6].status=='0','main_process':stateList[6].status=='2','sec_process':stateList[6].status=='3'}">
                打印报表
              </router-link>
              <div class="item un_activated" v-if="stateList[6].status=='-1'">打印报表</div>
            </el-row>
          </div>
        </div>
      </el-row>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default{
    data(){
      return {
        stateList: [
          {
            status: '0'
          }, {
            status: '0'
          }, {
            status: '0'
          }, {
            status: '0'
          }, {
            status: '0'
          }, {
            status: '0'
          }, {
            status: '0'
          }
        ],
        planId: ''
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getProcess',
        param: {
          planId: self.$route.params.id
        }
      };
      self.planId = self.$route.params.id;
      req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
        self.stateList = res.data;
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.go(-1);
      },
      publishedMsg(){
        var self = this, data = {
          planId: self.planId
        };
        self.$confirm('是否确定发布宿舍分配结果至学生？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/StudentDorm/publish', 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('发布成功!');
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }).catch(() => {
        });
      }
    }
  }
</script>
<style>
  .distributionProcess .steps {
    text-align: right;
    margin: 2rem 0 4.375rem;
  }

  .distributionProcess .step + .step {
    margin-left: 3.5rem;
  }

  .distributionProcess .step {
    position: relative;
    font-size: .875rem;
  }

  .distributionProcess .step:before {
    position: absolute;
    display: block;
    content: '';
    width: .6rem;
    height: .6rem;
    top: 50%;
    -webkit-transform: translateY(-50%);
    -moz-transform: translateY(-50%);
    -ms-transform: translateY(-50%);
    -o-transform: translateY(-50%);
    transform: translateY(-50%);
    left: -1.5rem;
    border-radius: 100%;
  }

  .distributionProcess .step.unable_edit:before, .distributionProcess .distributionProcess_item .item.unable_edit {
    background: #d2d2d2;
    color: #fff;
    text-align: center;
  }

  .distributionProcess .step.completed:before, .distributionProcess .distributionProcess_item .item.completed {
    background: #13b5b1;
    color: #fff;
    text-align: center;
  }

  .distributionProcess .step.main_process:before, .distributionProcess .distributionProcess_item .item.main_process {
    background: #4da1ff;
    color: #fff;
    text-align: center;
  }

  .distributionProcess .step.sec_process:before, .distributionProcess .distributionProcess_item .item.sec_process {
    background: #89bcf5;
    color: #fff;
    text-align: center;
  }

  .distributionProcess .step.un_activated:before, .distributionProcess .distributionProcess_item .item.un_activated {
    border: 1px solid #d2d2d2;
  }

  .distributionProcess .stepLists {
    width: 75rem;
    margin: 0 auto 10rem;
  }

  .distributionProcess .distributionProcess_items > div {
    float: left;
  }

  .distributionProcess .distributionProcess_item .item {
    display: inline-block;
    width: 15rem;
    padding: .75rem 0;
    border-radius: 1.5rem;
    font-size: .875rem;
    text-align: center;
  }

  .distributionProcess .spec_row {
    width: 15rem;
  }

  .distributionProcess .distributionProcess_item .item.completed, .distributionProcess .distributionProcess_item .item.main_process, .distributionProcess .distributionProcess_item .item.sec_process {
    cursor: pointer;
  }

  .distributionProcess .arrowDown, .distributionProcess .arrowRight, .distributionProcess .arrowTop, .distributionProcess .arrowRightBot {
    position: relative;
  }

  .distributionProcess .arrowDown i {
    position: absolute;
    bottom: -.8rem;
    left: -.6rem;
  }

  .distributionProcess .arrowRight i {
    position: absolute;
    top: -.5rem;
    right: -.6rem;
  }

  .distributionProcess .arrowRightBot i {
    position: absolute;
    right: -.5rem;
    bottom: -.5rem;
  }

  .distributionProcess .arrowTop i {
    position: absolute;
    top: -.6rem;
    right: -.4rem;
  }

  .distributionProcess .vertical_img {
    position: relative;
    width: 0;
    height: 2.5rem;
    margin: .8rem 0 1rem 0;
    left: 50%;
    -webkit-transform: translateX(-50%);
    -moz-transform: translateX(-50%);
    -ms-transform: translateX(-50%);
    -o-transform: translateX(-50%);
    transform: translateX(-50%);
    border-left: .125rem solid;
  }

  .distributionProcess .horizontal_img {
    display: inline-block;
    width: 2.5rem;
    margin: 1.5rem 1.2rem 1rem .8rem;
    border-bottom: .125rem solid;
  }

  .distributionProcess .arrowRight_inline.horizontal_img {
    margin: 0 1.2rem 0 .8rem;
  }

  .distributionProcess .spec_img {
    width: 11rem;
    margin-left: 7.5rem;
    height: 6.5rem;
    border-left: .125rem solid;
    border-bottom: .125rem solid;
  }

  .distributionProcess .arrowRightBot1 {
    margin-top: .8rem;
  }

  .distributionProcess .arrowRightBot1.spec_img {
    height: 5rem;
  }

  .distributionProcess .independent_img1 {
    display: inline-block;
    width: 12rem;
    height: 4.5rem;
    margin: 1.5rem 1.2rem .5rem 6.25rem;
    border-top: .125rem solid;
    border-left: .125rem solid;
  }

  .distributionProcess .horizontal_img.completed i, .distributionProcess .vertical_img.completed i, .distributionProcess .independent_img1.completed i, .distributionProcess .spec_img.completed i {
    color: #09baa7;
  }

  .distributionProcess .vertical_img.completed, .distributionProcess .horizontal_img.completed, .distributionProcess .independent_img1.completed, .distributionProcess .spec_img.completed {
    border-color: #09baa7;
  }

  .distributionProcess .horizontal_img.main_process i, .distributionProcess .vertical_img.main_process i, .distributionProcess .independent_img1.main_process i, .distributionProcess .spec_img.main_process i {
    color: #4da1ff;
  }

  .distributionProcess .vertical_img.main_process, .distributionProcess .horizontal_img.main_process, .distributionProcess .independent_img1.main_process, .distributionProcess .spec_img.main_process {
    border-color: #4da1ff;
  }

  .distributionProcess .horizontal_img.sec_process i, .distributionProcess .vertical_img.sec_process i, .distributionProcess .independent_img1.sec_process i, .distributionProcess .spec_img.sec_process i {
    color: #89bcf5;
  }

  .distributionProcess .vertical_img.sec_process, .distributionProcess .horizontal_img.sec_process, .distributionProcess .independent_img1.sec_process, .distributionProcess .spec_img.sec_process {
    border-color: #89bcf5;
  }

  .distributionProcess .horizontal_img.un_activated i, .distributionProcess .vertical_img.un_activated i, .distributionProcess .independent_img1.un_activated i, .distributionProcess .spec_img.un_activated i {
    color: #d2d2d2;
  }

  .distributionProcess .vertical_img.un_activated, .distributionProcess .horizontal_img.un_activated, .distributionProcess .independent_img1.un_activated, .distributionProcess .spec_img.un_activated {
    border-color: #d2d2d2;
  }

  .distributionProcess .horizontal_img.unable_edit i, .distributionProcess .vertical_img.unable_edit i, .distributionProcess .independent_img1.unable_edit i, .distributionProcess .spec_img.unable_edit i {
    color: #d2d2d2;
  }

  .distributionProcess .vertical_img.unable_edit, .distributionProcess .horizontal_img.unable_edit, .distributionProcess .independent_img1.unable_edit, .distributionProcess .spec_img.unable_edit {
    border-color: #d2d2d2;
  }

  .distributionProcess .topMax {
    margin-top: 6.8rem;
  }
</style>
