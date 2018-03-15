<template>
  <div class="subClassDivision">
    <h3>分科分班管理</h3>
    <el-row type="flex" align="middle" class="subClassDivision_row">
      <el-col :span="10">
        <el-form :inline="true">
          <el-form-item label="分班方案：">
            <el-select
              @change="getProgramme" v-model="param.planId" placeholder="请选择">
              <el-option
                v-for="item in options"
                :key="item.id"
                :label="item.name"
                :value="item.id">
              </el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </el-col>
      <el-col :span="14" class="steps">
        <span class="step unable_edit">不可编辑</span>
        <span class="step completed">已完成</span>
        <span class="step main_process">主要流程</span>
        <span class="step sec_process">次要流程</span>
        <span class="step un_activated ">未激活</span>
      </el-col>
    </el-row>
    <el-row class="stepLists">
      <el-row class="subClassDivision_items">
        <div>
          <div class="arrowDown independent_img1 unable_edit">
            <i class="el-icon-arrow-down"></i>
          </div>
        </div>
        <div>
          <el-row class="subClassDivision_item">
            <router-link v-if="stepsList[0].status!='-1'" :to="{name:'reviseSubClassPlan',params:{planId:param.planId}}"
                         tag="div" class="item"
                         :class="{'completed':stepsList[0].status=='1','unable_edit':stepsList[0].status=='0','main_process':stepsList[0].status=='2','sec_process':stepsList[0].status=='3'}">
              修改分班方案
            </router-link>
            <div class="item un_activated" v-if="stepsList[0].status=='-1'">修改分班方案</div>
          </el-row>
          <el-row>
            <div class="arrowDown vertical_img unable_edit">
              <i class="el-icon-arrow-down"></i>
            </div>
          </el-row>
          <el-row class="subClassDivision_item">
            <router-link v-if="stepsList[1].status!='-1'"
                         :to="{name:'subStudentManagement',params:{planId:param.planId}}" tag="div" class="item"
                         :class="{'completed':stepsList[1].status=='1','unable_edit':stepsList[1].status=='0','main_process':stepsList[1].status=='2','sec_process':stepsList[1].status=='3'}">
              分班学生管理
            </router-link>
            <div class="item un_activated" v-if="stepsList[1].status=='-1'">分班学生管理</div>
          </el-row>
          <el-row>
            <div class="arrowDown vertical_img unable_edit">
              <i class="el-icon-arrow-down"></i>
            </div>
          </el-row>
          <el-row class="subClassDivision_item">
            <router-link v-if="stepsList[2].status!='-1'" :to="{name:'subScoreBasis',params:{planId:param.planId}}"
                         tag="div" class="item"
                         :class="{'completed':stepsList[2].status=='1','unable_edit':stepsList[2].status=='0','main_process':stepsList[2].status=='2','sec_process':stepsList[2].status=='3'}">
              分班成绩依据
            </router-link>
            <div class="item un_activated" v-if="stepsList[2].status=='-1'">分班成绩依据</div>
          </el-row>
          <el-row>
            <div class="arrowDown vertical_img unable_edit">
              <i class="el-icon-arrow-down"></i>
            </div>
          </el-row>
        </div>
      </el-row>
      <el-row class="subClassDivision_items subClassDivision_item">
        <router-link v-if="stepsList[3].status!='-1'" :to="{name:'fillVolunteerSet',params:{planId:param.planId}}"
                     tag="div" class="item"
                     :class="{'completed':stepsList[3].status=='1','unable_edit':stepsList[3].status=='0','main_process':stepsList[3].status=='2','sec_process':stepsList[3].status=='3'}">
          填报志愿设置
        </router-link>
        <div class="item un_activated" v-if="stepsList[3].status=='-1'">填报志愿设置</div>
        <div class="arrowRight horizontal_img unable_edit">
          <i class="el-icon-arrow-right"></i>
        </div>
        <div>
          <router-link v-if="stepsList[4].status!='-1'" :to="{name:'scoreCountSet',params:{planId:param.planId}}"
                       tag="div" class="item"
                       :class="{'completed':stepsList[4].status=='1','unable_edit':stepsList[4].status=='0','main_process':stepsList[4].status=='2','sec_process':stepsList[4].status=='3'}">
            成绩统计设置
          </router-link>
          <div class="item un_activated" v-if="stepsList[4].status=='-1'">成绩统计设置</div>
          <div class="arrowDown vertical_img unable_edit">
            <i class="el-icon-arrow-down"></i>
          </div>
        </div>
        <div class="arrowRight horizontal_img unable_edit">
          <i class="el-icon-arrow-right"></i>
        </div>
        <router-link v-if="stepsList[5].status!='-1'" :to="{name:'aggregateScoreCount',params:{planId:param.planId}}"
                     tag="div" class="item"
                     :class="{'completed':stepsList[5].status=='1','unable_edit':stepsList[5].status=='0','main_process':stepsList[5].status=='2','sec_process':stepsList[5].status=='3'}">
          合计成绩统计
        </router-link>
        <div class="item un_activated" v-if="stepsList[5].status=='-1'">合计成绩统计</div>
      </el-row>
      <el-row class="subClassDivision_items subClassDivision_item">
        <div>
          <el-row class="arrowDown independent_img6 un_activated">
            <i class="el-icon-arrow-down"></i>
          </el-row>
          <el-row>
            <router-link v-if="stepsList[12].status!='-1'"
                         :to="{name:'specifiedStudentClass',params:{planId:param.planId}}" tag="div" class="item"
                         :class="{'completed':stepsList[12].status=='1','unable_edit':stepsList[12].status=='0','main_process':stepsList[12].status=='2','sec_process':stepsList[12].status=='3'}">
              指定学生到班
            </router-link>
            <div class="item un_activated" v-if="stepsList[12].status=='-1'">指定学生到班</div>
          </el-row>
        </div>
        <div>
          <el-row>
            <div class="item"
                 :class="{'completed':stepsList[6].status=='1','un_activated':stepsList[6].status=='-1','unable_edit':stepsList[6].status=='0','main_process':stepsList[6].status=='2','sec_process':stepsList[6].status=='3'}">
              学生填报志愿
            </div>
          </el-row>
          <el-row>
            <div class="arrowDown vertical_img un_activated">
              <i class="el-icon-arrow-down"></i>
            </div>
          </el-row>
          <el-row>
            <router-link v-if="stepsList[11].status!='-1'" :to="{name:'likeSubClassSet',params:{planId:param.planId}}"
                         tag="div" class="item"
                         :class="{'completed':stepsList[11].status=='1','unable_edit':stepsList[11].status=='0','main_process':stepsList[11].status=='2','sec_process':stepsList[11].status=='3'}">
              拟分班级设置
            </router-link>
            <div class="item un_activated" v-if="stepsList[11].status=='-1'">拟分班级设置</div>
          </el-row>
          <el-row class="arrowDown vertical_img un_activated">
            <i class="el-icon-arrow-down"></i>
          </el-row>
        </div>
        <div>
          <el-row class="arrowRight independent_img3 un_activated">
            <i class="el-icon-arrow-right"></i>
          </el-row>
          <el-row class="arrowDown independent_img4 un_activated">
            <i class="el-icon-arrow-down"></i>
          </el-row>
          <el-row type="flex" align="middle" class="spec">
            <router-link v-if="stepsList[7].status!='-1'"
                         :to="{name:'studentVolunteerRevise',params:{planId:param.planId}}" tag="div" class="item"
                         :class="{'completed':stepsList[7].status=='1','unable_edit':stepsList[7].status=='0','main_process':stepsList[7].status=='2','sec_process':stepsList[7].status=='3'}">
              学生志愿调整
            </router-link>
            <div class="item un_activated" v-if="stepsList[7].status=='-1'">学生志愿调整</div>
            <div class="arrowRight horizontal_img un_activated">
              <i class="el-icon-arrow-right"></i>
            </div>
          </el-row>
          <el-row class="arrowLeft independent_img5 un_activated">
            <i class="el-icon-arrow-left"></i>
          </el-row>
        </div>
        <div class="group un_activated">
          <el-row>
            <router-link v-if="stepsList[8].status!='-1'"
                         :to="{name:'studentVolunteerCount',params:{planId:param.planId}}" tag="div" class="item"
                         :class="{'completed':stepsList[8].status=='1','unable_edit':stepsList[8].status=='0','main_process':stepsList[8].status=='2','sec_process':stepsList[8].status=='3'}">
              学生志愿统计
            </router-link>
            <div class="item un_activated" v-if="stepsList[8].status=='-1'">学生志愿统计</div>
          </el-row>
          <el-row>
            <router-link v-if="stepsList[9].status!='-1'"
                         :to="{name:'unFillVolunteerList',params:{planId:param.planId}}" tag="div" class="item"
                         :class="{'completed':stepsList[9].status=='1','unable_edit':stepsList[9].status=='0','main_process':stepsList[9].status=='2','sec_process':stepsList[9].status=='3'}">
              未报志愿学生名单
            </router-link>
            <div class="item un_activated" v-if="stepsList[9].status=='-1'">未报志愿学生名单</div>
          </el-row>
          <el-row>
            <router-link v-if="stepsList[10].status!='-1'"
                         :to="{name:'volunteerConfirmList',params:{planId:param.planId}}" tag="div" class="item"
                         :class="{'completed':stepsList[10].status=='1','unable_edit':stepsList[10].status=='0','main_process':stepsList[10].status=='2','sec_process':stepsList[10].status=='3'}">
              志愿确认签名单
            </router-link>
            <div class="item un_activated" v-if="stepsList[10].status=='-1'">志愿确认签名单</div>
          </el-row>
        </div>
      </el-row>
      <el-row class="subClassDivision_items subClassDivision_item">
        <div class="arrowRightT independent_img7 un_activated">
          <i class="el-icon-arrow-right"></i>
        </div>
        <div>
          <el-row>
            <router-link v-if="stepsList[13].status!='-1'" :to="{name:'quicklySplitclass',params:{planId:param.planId}}"
                         tag="div" class="item"
                         :class="{'completed':stepsList[13].status=='1','unable_edit':stepsList[13].status=='0','main_process':stepsList[13].status=='2','sec_process':stepsList[13].status=='3'}">
              快速分班
            </router-link>
            <div class="item un_activated" v-if="stepsList[13].status=='-1'">快速分班</div>
          </el-row>
          <el-row>
            <div class="arrowDown vertical_img un_activated">
              <i class="el-icon-arrow-down"></i>
            </div>
          </el-row>
          <el-row>
            <router-link v-if="stepsList[16].status!='-1'"
                         :to="{name:'publishSubClassResult',params:{planId:param.planId}}" tag="div" class="item"
                         :class="{'completed':stepsList[16].status=='1','unable_edit':stepsList[16].status=='0','main_process':stepsList[16].status=='2','sec_process':stepsList[16].status=='3'}">
              发布分班结果
            </router-link>
            <div class="item un_activated" v-if="stepsList[16].status=='-1'">发布分班结果</div>
          </el-row>
          <el-row>
            <div class="arrowDown vertical_img un_activated">
              <i class="el-icon-arrow-down"></i>
            </div>
          </el-row>
          <el-row>
            <div class="item" v-if="stepsList[17].status!='-1'" @click="synchronizingData"
                 :class="{'completed':stepsList[17].status=='1','unable_edit':stepsList[17].status=='0','main_process':stepsList[17].status=='2','sec_process':stepsList[17].status=='3'}">
              同步分科分班数据
            </div>
            <div class="item un_activated" v-if="stepsList[17].status=='-1'">同步分科分班数据</div>
          </el-row>
        </div>
        <div>
          <el-row class="arrowRightT arrowLine un_activated">
          </el-row>
          <el-row class="subClassDivision_items">
            <div>
              <div class="arrowDown independent_img4 un_activated">
                <i class="el-icon-arrow-down"></i>
              </div>
              <router-link v-if="stepsList[14].status!='-1'"
                           :to="{name:'manuallyAdjustment',params:{planId:param.planId}}" tag="div" class="item spec"
                           :class="{'completed':stepsList[14].status=='1','unable_edit':stepsList[14].status=='0','main_process':stepsList[14].status=='2','sec_process':stepsList[14].status=='3'}">
                手动调整
              </router-link>
              <div class="item spec un_activated" v-if="stepsList[14].status=='-1'">手动调整</div>
              <div class="arrowLeft independent_img5 un_activated">
                <i class="el-icon-arrow-left"></i>
              </div>
            </div>
            <div>
              <div class="arrowDown independent_img4 un_activated el-row">
                <i class="el-icon-arrow-down"></i>
              </div>
              <div>
                <div class="arrowRight horizontal_imgs un_activated">
                  <i class="el-icon-arrow-right"></i>
                </div>
                <router-link v-if="stepsList[15].status!='-1'" :to="{name:'printReport',params:{planId:param.planId}}"
                             tag="div" class="item"
                             :class="{'completed':stepsList[15].status=='1','unable_edit':stepsList[15].status=='0','main_process':stepsList[15].status=='2','sec_process':stepsList[15].status=='3'}">
                  打印报表
                </router-link>
                <div class="item un_activated" v-if="stepsList[15].status=='-1'">打印报表</div>
              </div>
            </div>
          </el-row>
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
        options: [],
        param: {
          planId: ''
        },
        stepsList: [{
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }, {
          status: '-1'
        }]
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getAllPlan'
      };
      //获取分班方案
      req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
        self.options = res;
        self.param.planId = res[0].id;
        if(sessionStorage.getItem('planId')){
          self.param.planId= sessionStorage.getItem('planId');
        }
        //获取各个流程的状态
        req.ajaxSend('/school/DivideBranch/getAllProcess', 'post', self.param, function (res) {
          self.stepsList = res;
        })
      });
    },
    methods: {
      getProgramme(){
        var self = this;
        sessionStorage.setItem('planId',self.param.planId);
        req.ajaxSend('/school/DivideBranch/getAllProcess', 'post', self.param, function (res) {
          self.stepsList = res;
        })
      },
      synchronizingData(){  //同步分班分科数据
        var self = this, data = {
          planId: self.param.planId
        };
        self.$confirm('选择”确定“后将替换系统的已有信息, 是否确定?（确定后将无法还原系统原有的学生和班级信息）', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/DivideBranch/sync', 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess(res.msg);
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
  .subClassDivision .steps {
    text-align: right;
  }

  .subClassDivision .step + .step {
    margin-left: 3.5rem;
  }

  .subClassDivision .step {
    position: relative;
    font-size: .875rem;
  }

  .subClassDivision .step:before {
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

  .subClassDivision .step.unable_edit:before, .subClassDivision .subClassDivision_item .item.unable_edit {
    background: #d2d2d2;
    color: #fff;
    text-align: center;
  }

  .subClassDivision .step.completed:before, .subClassDivision .subClassDivision_item .item.completed {
    background: #13b5b1;
    color: #fff;
    text-align: center;
  }

  .subClassDivision .step.main_process:before, .subClassDivision .subClassDivision_item .item.main_process {
    background: #4da1ff;
    color: #fff;
    text-align: center;
  }

  .subClassDivision .step.sec_process:before, .subClassDivision .subClassDivision_item .item.sec_process {
    background: #89bcf5;
    color: #fff;
    text-align: center;
  }

  .subClassDivision .step.un_activated:before, .subClassDivision .subClassDivision_item .item.un_activated {
    border: 1px solid #d2d2d2;
  }

  .subClassDivision .stepLists {
    width: 70rem;
    margin: auto;
  }

  .subClassDivision .subClassDivision_items > div {
    float: left;
  }

  .subClassDivision .subClassDivision_item .item {
    display: inline-block;
    width: 12.5rem;
    padding: .75rem 0;
    border-radius: 1.5rem;
    font-size: .875rem;
    text-align: center;
  }

  .subClassDivision .subClassDivision_item .item.completed, .subClassDivision .subClassDivision_item .item.main_process, .subClassDivision .subClassDivision_item .item.sec_process {
    cursor: pointer;
  }

  .subClassDivision .arrowDown, .subClassDivision .arrowRight, .subClassDivision .arrowLeft, .subClassDivision .arrowRightT {
    position: relative;
  }

  .subClassDivision .arrowDown i {
    position: absolute;
    bottom: -.8rem;
    left: -.6rem;
  }

  .subClassDivision .arrowRight i {
    position: absolute;
    top: -.5rem;
    right: -.6rem;
  }

  .subClassDivision .arrowLeft i {
    position: absolute;
    bottom: -.5rem;
    left: -.6rem;
  }

  .subClassDivision .arrowRightT i {
    position: absolute;
    bottom: -.5rem;
    right: -.6rem;
  }

  .subClassDivision .vertical_img {
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

  .subClassDivision .horizontal_img {
    display: inline-block;
    width: 2.5rem;
    margin: 1.5rem 1.2rem 1.5rem .8rem;
    border-bottom: .125rem solid;
  }

  .subClassDivision .horizontal_imgs {
    display: inline-block;
    width: 2.5rem;
    margin: 0 .8rem;
    border-bottom: .125rem solid;
  }

  .subClassDivision .independent_img1 {
    width: 10rem;
    height: 18.5rem;
    margin: 1.5rem .8rem 0 6.2rem;
    border-top: .125rem solid;
    border-left: .125rem solid;
  }

  .subClassDivision .independent_img3 {
    width: 19.375rem;
    margin: 1rem .8rem 0;
    border-bottom: .125rem solid;
  }

  .subClassDivision .independent_img4 {
    width: 0;
    height: 1.2rem;
    margin: 0 0 1rem 10.25rem;
    border-left: .125rem solid;
  }

  .subClassDivision .independent_img5 {
    width: 9.4rem;
    height: 1.3rem;
    margin: .8rem 1rem;
    border-right: .125rem solid;
    border-bottom: .125rem solid;
  }

  .subClassDivision .independent_img6 {
    width: 9.4rem;
    height: 1.625rem;
    margin: 8.5rem 1.2rem .8rem 6.25rem;
    float: right;
    border-left: .125rem solid #09baa7;
    border-top: .125rem solid #09baa7;
  }

  .subClassDivision .independent_img7 {
    width: 9.4rem;
    height: 1.625rem;
    margin: 0 1rem .8rem 6.25rem;
    border-left: .125rem solid;
    border-bottom: .125rem solid;
  }

  .subClassDivision .arrowLine {
    width: 26.5rem;
    margin-left: .8rem;
    margin-top: 1.5rem;
    border-top: .125rem solid;
  }

  .subClassDivision .group {
    border-left: 1px solid;
    border-right: 1px solid;
    margin-top: .5rem;
  }

  .subClassDivision .group > div {
    position: relative;
  }

  .subClassDivision .group > div:first-child {
    top: -1rem;
  }

  .subClassDivision .group > div:last-child {
    bottom: -1rem;
  }

  .subClassDivision .spec > div:first-child {
    margin-left: 4rem;
  }

  .subClassDivision .item.spec {
    margin-left: 4.25rem;
  }

  .subClassDivision .horizontal_img.completed i, .subClassDivision .horizontal_imgs.completed i, .subClassDivision .vertical_img.completed i, .subClassDivision .independent_img1.completed i, .subClassDivision .independent_img3.completed i, .subClassDivision .independent_img4.completed i, .subClassDivision .independent_img5.completed i, .subClassDivision .independent_img6.completed i, .subClassDivision .independent_img7.completed i {
    color: #09baa7;
  }

  .subClassDivision .horizontal_imgs.completed, .subClassDivision .vertical_img.completed, .subClassDivision .horizontal_img.completed, .subClassDivision .independent_img1.completed, .subClassDivision .independent_img3.completed, .subClassDivision .independent_img4.completed, .subClassDivision .independent_img5.completed, .subClassDivision .independent_img6.completed, .subClassDivision .independent_img7.completed, .subClassDivision .arrowLine.completed, .subClassDivision .group.completed {
    border-color: #09baa7;
  }

  .subClassDivision .horizontal_img.main_process i, .subClassDivision .horizontal_imgs.main_process i, .subClassDivision .vertical_img.main_process i, .subClassDivision .independent_img1.main_process i, .subClassDivision .independent_img3.main_process i, .subClassDivision .independent_img4.main_process i, .subClassDivision .independent_img5.main_process i, .subClassDivision .independent_img6.main_process i, .subClassDivision .independent_img7.main_process i {
    color: #4da1ff;
  }

  .subClassDivision .horizontal_imgs.main_process, .subClassDivision .vertical_img.main_process, .subClassDivision .horizontal_img.main_process, .subClassDivision .independent_img1.main_process, .subClassDivision .independent_img3.main_process, .subClassDivision .independent_img4.main_process, .subClassDivision .independent_img5.main_process, .subClassDivision .independent_img6.main_process, .subClassDivision .independent_img7.main_process, .subClassDivision .arrowLine.main_process, .subClassDivision .group.main_process {
    border-color: #4da1ff;
  }

  .subClassDivision .horizontal_img.sec_process i, .subClassDivision .horizontal_imgs.sec_process i, .subClassDivision .vertical_img.sec_process i, .subClassDivision .independent_img1.sec_process i, .subClassDivision .independent_img3.sec_process i, .subClassDivision .independent_img4.sec_process i, .subClassDivision .independent_img5.sec_process i, .subClassDivision .independent_img6.sec_process i, .subClassDivision .independent_img7.sec_process i {
    color: #89bcf5;
  }

  .subClassDivision .horizontal_imgs.sec_process, .subClassDivision .vertical_img.sec_process, .subClassDivision .horizontal_img.sec_process, .subClassDivision .independent_img1.sec_process, .subClassDivision .independent_img3.sec_process, .subClassDivision .independent_img4.sec_process, .subClassDivision .independent_img5.sec_process, .subClassDivision .independent_img6.sec_process, .subClassDivision .independent_img7.sec_process, .subClassDivision .arrowLine.sec_process, .subClassDivision .group.sec_process {
    border-color: #89bcf5;
  }

  .subClassDivision .horizontal_img.un_activated i, .subClassDivision .horizontal_imgs.un_activated i, .subClassDivision .vertical_img.un_activated i, .subClassDivision .independent_img1.un_activated i, .subClassDivision .independent_img3.un_activated i, .subClassDivision .independent_img4.un_activated i, .subClassDivision .independent_img5.un_activated i, .subClassDivision .independent_img6.un_activated i, .subClassDivision .independent_img7.un_activated i {
    color: #d2d2d2;
  }

  .subClassDivision .horizontal_imgs.un_activated, .subClassDivision .vertical_img.un_activated, .subClassDivision .horizontal_img.un_activated, .subClassDivision .independent_img1.un_activated, .subClassDivision .independent_img3.un_activated, .subClassDivision .independent_img4.un_activated, .subClassDivision .independent_img5.un_activated, .subClassDivision .independent_img6.un_activated, .subClassDivision .independent_img7.un_activated, .subClassDivision .arrowLine.un_activated, .subClassDivision .group.un_activated {
    border-color: #d2d2d2;
  }

  .subClassDivision .horizontal_img.unable_edit i, .subClassDivision .horizontal_imgs.unable_edit i, .subClassDivision .vertical_img.unable_edit i, .subClassDivision .independent_img1.unable_edit i, .subClassDivision .independent_img3.unable_edit i, .subClassDivision .independent_img4.unable_edit i, .subClassDivision .independent_img5.unable_edit i, .subClassDivision .independent_img6.unable_edit i, .subClassDivision .independent_img7.unable_edit i {
    color: #d2d2d2;
  }

  .subClassDivision .horizontal_imgs.unable_edit, .subClassDivision .vertical_img.unable_edit, .subClassDivision .horizontal_img.unable_edit, .subClassDivision .independent_img1.unable_edit, .subClassDivision .independent_img3.unable_edit, .subClassDivision .independent_img4.unable_edit, .subClassDivision .independent_img5.unable_edit, .subClassDivision .independent_img6.unable_edit, .subClassDivision .independent_img7.unable_edit, .subClassDivision .arrowLine.unable_edit, .subClassDivision .group.unable_edit {
    border-color: #d2d2d2;
  }
</style>
